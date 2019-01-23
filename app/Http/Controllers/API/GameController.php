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
        //
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
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        $game = Game::find($id);
        if ($game) {
            $game->start = $request->input('start');
            $game->save();
            return response()->json(['message'=>'Game updated successfully']);
        }
        return $this->response->errorNotFound();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
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
