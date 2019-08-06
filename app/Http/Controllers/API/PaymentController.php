<?php

namespace App\Http\Controllers\API;

use App\Club;
use App\Game;
use App\Notifications\GameConfirmed;
use App\Notifications\PaymentReceived;
use App\Payment;
use App\Sdks\MpesaSdk;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class PaymentController extends Controller
{
    public $mpesa;

    public function __construct(MpesaSdk $mpesaSdk)
    {
        $this->mpesa = $mpesaSdk;
    }
    public function validatePayload(Request $request)
    {
        Log::info("Mpesa validation endpoint from {$request->ip()}", $request->all());

        return response()->json(['ResultCode' => 0, 'ResultDesc' => "Service request accepted successfully"], 200);
    }

    public function confirmationPayload(Request $request)
    {
        Log::info("Mpesa confirmation endpoint from {$request->ip()}", $request->all());
        try {
            if(!$this->mpesa->validate_request($request))
                return response()->json([
                    'status' => 'fail',
                    'message' => "Invalid request"
                ], 403);

            $result  =  MpesaSdk::process_c2b_callback($request->all());

            $validator = Validator::make($result,
                [
                    'transaction_number' => 'required|unique:payments,transaction_number'
                ]);

            if ($validator->fails()) {
                return response()->json(['ResultCode' => 422, 'ResultDesc' => "Payment already received"],  422);
            }

            $payment = Payment::create([
                'transaction_number' => $result['transaction_number'],
                'transaction_date' => $result['transaction_date'],
                'amount' => $result['amount'],
                'account' => $result['account'],
                'phone' =>  $result['phone'],
                'name' => $result['name'],
                'source' => 'mpesa',
                'status' => 'complete'
            ]);
            $this->updatePlayerPaid($result['account'],$payment);
            return response()->json(['ResultCode' => 0, 'ResultDesc' => "Service request accepted successfully"], 200);

        } catch(\Exception $exception) {
            Log::error("Payment Processing", $request->all());
            report($exception);
            return response()->json(['ResultCode' => 500, 'ResultDesc' => "Error processing payment"], 200);
        }
    }

    public function updatePlayerPaid($account,$payment){
        $gameuser = DB::table('game_user')->where('account',$account)->first();

        $gamers = DB::table('game_user')->where('game_id',$gameuser->game_id)->get();

        $player_ids = $gamers->pluck('user_id');

        $confirmed_player_ids = $gamers->where('paid',1)->pluck('user_id');

        $unconfirmed_player_ids = $gamers->where('paid',0)->pluck('user_id');

        $players = User::whereIn('id',$player_ids)->get();

        $confirmed_players = $players->whereIn($confirmed_player_ids);

        $unconfirmed_players = $players->whereIn($unconfirmed_player_ids);

        DB::table('game_user')->where('id',$gameuser->id)->update(['paid' => 1 ]);

        $user = User::find($gameuser->user_id);
        $game = Game::find($gameuser->game_id)->load('club');
        if(!$game->active){
            $game->active = 1;
            $game->save();
        }

        $user->notify(new PaymentReceived($user, $game,$payment));

        Notification::send($players, new GameConfirmed($user,$game,$confirmed_players,$unconfirmed_players));
    }

    public function queueTimeout(Request $request)
    {
        Log::info("Mpesa queue timeout endpoint", $request->all());
        return response()->json(['ResultCode' => 0, 'ResultDesc' => "Service request accepted successfully"], 200);
    }

    public function stkCallback(Request $request, $ref)
    {
        try {
            Log::info("Mpesa stk from {$request->ip()}", $request->all());
            if(!$this->mpesa->validate_request($request))
                return response()->json([
                    'status' => 'fail',
                    'message' => "Invalid request"
                ], 403);
        } catch(\Exception $exception) {
            Log::error("Payment Processing ({$ref}): {$exception->getMessage()}", [
                'params' => $request->all(),
//                'result' => $result
            ]);
        }
    }

    public function pay(Request $request)
    {
        $this->validate($request, [
            'amount' => "required|numeric|min:10",
            'phone' => "nullable|sometimes|phone:KE",
            'account' => "required|exists:game_user,account"
        ]);

        $user = $request->user();
        $gameuser = DB::table('game_user')->where('account',$request->account)->first();

        # validate game exist
        if (!$gameuser) {
            return response()->json([
                'status' => 'fail',
                'message' => "No such game found"
            ], 430);
        }
        $game = Game::find($gameuser->game_id);

        $member = $user->clubs()->where('club_id', $game->club_id);

        if($member){
            $rate = Club::find($game->club_id)->membersRate();
        }else{
            $rate = Club::find($game->club_id)->nonMembersRate();
        }

        # Get any payments
        $payment = Payment::where('account',$request->account)->sum('amount');
        $rate = $rate - $payment;

        try {
            $sdk = new MpesaSdk(null, false);

            $phone = encode_phone_number($request->phone ?: $user->phone);

            $res = $sdk->stk_push($phone, $rate, $request->account, 'Payment');

            $data = json_decode($res, true);

            if ($data['ResponseCode'] == 0) {
                return response()->json([
                    'status' => 'ok',
                    'message' => "STK push sent"
                ], 200);
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => "Failed to send STK push"
                ], 430);
            }
        } catch (\Exception $exception) {
            //throw ($exception);
            return response()->json([
                'status' => 'fail',
                'message' => "Something nasty must have happened.",
                'error' => $exception->getMessage()
            ], 430);
        }
    }

}
