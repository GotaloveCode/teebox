<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Game;
use App\Transformers\GameTransformer;


class GameController extends BaseController
{
    public function available()
    {
        //
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $games = $user->games()->get();
        return response()->json(['games' => $games],200);
    }



    public function create(Request $request)
    {
        $this->validate($request,[
            'club_id' => 'required|exists:clubs,club_id',
            'start' => 'required|date_format:d/m/Y H:i',
            'game_type' => 'required|in:9 hole,18 hole'
        ]);
        $start= Carbon::createFromFormat('d/m/Y H:i', $request->input('start'));
        $game_type = $request->input('game_type');

        if($game_type == "9 hole"){
            $end = $start->addHours(1.5);
        }else{
            $end = $start->addHours(3);
        }

        $game = Game::create([
            'club_id' => $request->input('club_id'),
            'start' => $start,
            'end' => $end,
            'game_type' => $game_type
        ]);

        $user = auth()->user();

        // #TODO Payments first

        $game->user()->attach($user->id);

        return response()->json(['message' => 'Booking done successfully']);
    }


    public function sendInvite(Request $request)
    {
         $this->validate($request,[
            'game_id' => 'required|exists:games,id',
            'user_id' => 'required|exists:users,id'
        ]);

        $game = Game::whereId($request->game_id);

        $user = auth()->user();

        if($game->user()->first()->id != $user->id){
            return response()->json(['status' => 'error','message' => 'The invite can only be sent by the game creator'],422);
        }

        $players_array [] = $game->user()->pluck('ids');
        if(!$players_array.contains($request->user_id)){
            $message = "Dear Customer, you have been invited for a golf game by {$user->first_name}, {$user->phone} at {$game->start}";
            dispatch(new SendSMS($phone, $message));
        }
    }

    public function update(Request $request, $id)
    {
        $game = Game::find($id);
        if ($game) {
            $game->start = $request->input('start');
            $game->end = $request->input('end');
            $game->game_type = $request->input('game_type');
            $game->save();
            return response()->json(['message'=>'Game updated successfully']);
        }
        return $this->response->errorNotFound();
    }

    
    public function getBooked(Request $request, $id)
    {
        $this->validate($request,[
            'start' => 'required|date_format:d/m/Y'
        ]);
        $start= Carbon::createFromFormat('d/m/Y', $request->input('start'));
        $request->input('start');

        $games = Game::with('game')->where('start', '>=', $start->startOfDay())
                ->where('end', '<=', $start->endOfDay())
                ->where('club_id',$id)
                ->get();

        return $this->collection($games, new GameTransformer());
    }
}
