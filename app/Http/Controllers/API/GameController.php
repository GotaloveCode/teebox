<?php

namespace App\Http\Controllers\API;

use App\Club;
use App\Jobs\SendSms;
use App\Payment;
use App\User;
use Illuminate\Http\Request;
use App\Game;
use Carbon\Carbon;
use App\Transformers\GameTransformer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class GameController extends BaseController
{
    protected $user;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user= auth()->user();
            return $next($request);
        });
    }

    public function available(Request $request)
    {
        $this->validate($request,[
            'club_id' => 'required|exists:clubs,id',
            'start' => 'required|date|after:yesterday',
            'end' => 'required|date|after_or_equal:start'
        ]);
        $start = Carbon::parse($request->input('start'));
        $end = Carbon::parse($request->input('end'));
        $games = Club::find($request->input('club_id'))->games()->withCount('players')
                    ->where('start','>=',$start)
                    ->where('end','<=',$end)
                    ->get();
//        $games = $games->load('players');
        return response()->json([
            'games' => $games
        ]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $games = $this->user->games()->get();
        return $this->collection($games,GameTransformer::class);
    }

    public function create(Request $request)
    {
        $this->validate($request,[
            'club_id' => 'sometimes|exists:clubs,id',
            'start' => 'bail|required|date|after:yesterday',
            'game_type' => 'required|in:9 hole,18 hole',
            'account' => 'sometimes|exists:games,account'
        ],[
            'account.exists' => 'Account does match any game'
        ]);

        $start = Carbon::createFromFormat('Y-m-d H:i:s', $request->start);

        $validator = Validator::make([],[]);
        $games_count = Game::where('start',$start)->count();
        if($games_count > 1){
            $validator->errors()->add('start',"No available games starting at {$start}");
            return response()->json([
                    'message' => 'Invalid Input',
                    'errors' => $validator->errors(),
                ],422);
        }

        $game_type = $request->game_type;

        if($game_type == "9 hole"){
            $end = $start->copy()->addHours(1);
            $end = $end->addMinutes(30);
        }else{
            $end = $start->copy()->addHours(3);
        }

        $account = $request->input('account');

        if($account){
            // game exists
            $game = Game::where('account',$account)->first();
            if($game->remainingPlayers() == 0){
                return response()->json([
                    'message' => 'Game fully booked. Make a new booking'
                ], 430);
            }
            // get unique account for game user
            $u_account = getToken();
            while(DB::table('game_user')->where('account', $u_account)->count() > 0){
                $u_account = getToken();
            }
            $game->players()->attach($this->user->id,['account' => $u_account]);
            return response()->json([
                'message' => 'Make payment to complete the booking',
                'account' => $account
            ]);
        }else{
            $account = getToken();
            while(Game::where('account', $account)->count() > 0){
                $account = getToken();
            }
        }

        try {
            DB::beginTransaction();

            $game = Game::create([
                'club_id' => $request->club_id,
                'start' => $start,
                'end' => $end,
                'account' => $account,
                'game_type' => $game_type
            ]);


            $game->players()->attach($this->user->id,['account' => $account]);

            DB::commit();

            return response()->json([
                'message' => 'Make payment to complete the booking',
                'account' => $account
            ]);

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::Error($exception);
            return response()->json([
                'message' => "Could not complete game booking. Try again"
            ], 430);
        }
    }


    public function sendInvite(Game $game,Request $request)
    {
        $this->validate($request,[
            'user_id' => 'required|exists:users,id'
        ]);
        $players_count = $game->players()->count();
        $validator = Validator::make([], []);
        $validator->after(function ($validator) use ($players_count) {
            if ($players_count == 4) {
                $validator->errors()->add('players', 'The maximum number of players per individual game is 4');
            }
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $user = auth()->user();
        $players_array = $game->players()->pluck('users.id');

        if(!$players_array->contains($request->user_id)){
            $account = getToken(6);
            while(DB::table('game_user')->where('account', $account)->count() > 0){
                $account = getToken(6);
            }
            $game->players()->attach($request->user_id,['account' => $account]);
            $new_player = User::find($request->user_id);
            $club = $game->club()->first()->name;
            $message = "Dear $new_player->first_name, you have been invited for a golf game by {$user->first_name} at {$club} {$game->start}";
            dispatch(new SendSMS($new_player->phone, $message));
            return response()->json(['message' =>"Invite successfully sent to {$new_player->FullName}"] );
        }else{
            return response()->json(['message' =>'Invite already sent']);
        }

    }

    public function update(Request $request, Game $game)
    {
        $this->validate($request,[
            'club_id' => 'required|exists:clubs,id',
            'start' => 'required|date|after:yesterday',
            'game_type' => 'required|in:9 hole,18 hole'
        ]);

        $start = Carbon::createFromFormat('Y-m-d H:i:s', $request->start);
        $game_type = $request->game_type;

        if($game_type == "9 hole"){
            $end = $start->addHours(1.5);
        }else{
            $end = $start->addHours(3);
        }
        $game->start = $start;
        $game->end = $end;
        $game->type = $game_type;
        $game->save();
        $user = auth()->user();
        $message = "Dear Customer, your game has been updated to start time: {$game->start} at 
           {$game->club()->first()->name} by  {$user->first_name}, {$user->phone}";
        $players = $game->players()->where('users.id','!=',$user->id)->pluck('phone')->toArray();

        for ($i = 0; $i< sizeOf($players); $i++){
            dispatch(new SendSMS($players[$i], $message));
        }
        return response()->json(['message'=>'Game updated successfully']);
    }

    
    public function getBooked()
    {
        $user = auth()->user(); 
        $start= Carbon::today()->startOfDay();
        $games = Game::with('players')->whereDate('start', '>=', $start)
                ->where('end', '<=', $start->endOfDay())
                ->where('user_id',$user->id)
                ->orderBy('start')
                ->get();

        return $this->collection($games, new GameTransformer());
    }

    public function paymentDetails(Request $request,$account){
        $game_user = DB::table('game_user')->where('account', $account)->first();
        if(!$game_user){
            return response()->json([
                'status' => 'fail',
                'message' => "No game found"
            ], 430);
        }
        $game = Game::find($game_user->game_id);

        $user = $request->user();

        $member = $user->clubs()->where('club_id', $game->club_id);

        $club = Club::find($game->club_id);
        if($member){
            $rate = $club->membersRate();
        }else{
            $rate = $club->nonMembersRate();
        }
        # Get any payments
        $payment = Payment::where('account',$request->account)->sum('amount');
        $rate = $rate - $payment;
        $club->paymentModes()->first();
        $paybill = $club->paymentModes()->first();
        if($paybill){
            $paybill_details['name'] = $paybill->name;
            $paybill_details['account'] = $paybill->account;
        }else{
            $paybill_details['name'] = config('mpesa.paybill_name');
            $paybill_details['account'] = config('mpesa.paybill_account');
        }
        return response()->json([
            'amount' => $rate,
            'payment_details' => $paybill_details
        ]);
    }
}
