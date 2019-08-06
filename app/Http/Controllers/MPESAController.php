<?php

namespace App\Http\Controllers;

use App\Game;
use App\Ticket;
use Illuminate\Http\Request;
use MPESA;
use App\Payment;
use Log;
use DB;
use Carbon\Carbon;
use function strtoupper;

class MPESAController extends Controller
{
    public function registerC2B(Request $request){
        //$mpesa = MPESA::registerC2bUrl();
        //$mpesa = MPESA::stkPush('254723238631', 1, 'CBL Test');

        //dd($mpesa);
    }

    public function c2bValidation(Request $request){
        $response = json_decode($request->getContent(), true); // remove true

        $message = ["ResultCode" => 0, "ResultDesc" => "Success", "ThirdPartyTransID" => ''];

        Log::info('C2B Validation: '. request()->ip());
        Log::info($response);

        return response()->json($message);
    }

    public function c2bConfirmation(Request $request){

        $response = json_decode($request->getContent(), true); // remove true

        $mpesa_transaction_id = $response['TransID'];
        $date_time = Carbon::parse($response['TransTime']);
        $amount = $response['TransAmount'];
        $account = preg_replace('/\s+/', '', $response['BillRefNumber']);
        //$transaction_id = $response['ThirdPartyTransID'];
        $phone = $response['MSISDN'];
        $payer = preg_replace('!\s+!', ' ', ucwords(strtolower($response['FirstName'].' '.$response['MiddleName'].' '.$response['LastName'])));

        if(!$mpesa_transaction_id || !$date_time || !$amount || !$account || !$phone || !$payer){
            return response()->json(["ResultCode" => 1, "ResultDesc" => "Failure"]);
        }

        $exists = Payment::where('transaction_number', $mpesa_transaction_id)->count();
        if($exists == 0){
            $payment = new Payment;
            $payment->transaction_number = $mpesa_transaction_id;
            $payment->transaction_date = $date_time;
            $payment->amount = $amount;
            $payment->account = strtoupper($account);
            $payment->name = $payer;
            $payment->phone = $phone;
            $payment->save();

            $game = Game::where('account', strtoupper($account))->first();
            $game->active = 1;
            $game->save();
            $player = $game->players()->where('account',strtoupper($account))->first();
            DB::
            $game->players()->attach($player->id,['paid' =>1,'account'=>strtoupper($account)]);
        }
        $message = ["ResultCode" => 0, "ResultDesc" => "Success"];


        Log::info('C2B Confirmation: '. request()->ip());
        Log::info($response);

        return response()->json($message);
    }

    public function trxStatusTimeout(Request $request){
        $response = json_decode($request->getContent(), true); // remove true
        Log::info('TRX Timeout: '. request()->ip());
        Log::info($response);
        $message = ["ResultCode" => "00000000", "ResultDesc" => "Success"];
        return response()->json($message);
    }

    public function trxStatusConfirmation(Request $request){
        $response = json_decode($request->getContent(), true); // remove true
        Log::info('TRX Confirmation: '. request()->ip());
        Log::info($response);
        $message = ["ResultCode" => "00000000", "ResultDesc" => "Success"];
        return response()->json($message);
    }

    public function reversalTimeout(Request $request){
        $response = json_decode($request->getContent(), true); // remove true
        Log::info('Reversal Timeout: '. request()->ip());
        Log::info($response);
        $message = ["ResultCode" => "00000000", "ResultDesc" => "Success"];
        return response()->json($message);
    }

    public function reversalConfirmation(Request $request){
        $response = json_decode($request->getContent(), true); // remove true
        Log::info('Reversal Confirmation: '. request()->ip());
        Log::info($response);

        $message = ["ResultCode" => "00000000", "ResultDesc" => "Success"];
        return response()->json($message);
    }

    public function accBalTimeout(Request $request){
        $response = json_decode($request->getContent(), true); // remove true
        Log::info('Acc Bal Timeout: '. request()->ip());
        Log::info($response);
        $message = ["ResultCode" => "00000000", "ResultDesc" => "Success"];
        return response()->json($message);
    }

    public function accBalConfirmation(Request $request){
        $response = json_decode($request->getContent(), true); // remove true
        Log::info('Acc Bal Confirmation: '. request()->ip());
        Log::info($response);

        $message = ["ResultCode" => "00000000", "ResultDesc" => "Success"];
        return response()->json($message);
    }

    public function stkConfirmation(Request $request){
        $response = json_decode($request->getContent(), true); // remove true
        Log::info('STK Confirmation: '. request()->ip());
        Log::info($response);

        if($response['Body']['stkCallback']['ResultCode'] == 1032){
            //'User Cancelled'
            $checkout = $response['Body']['stkCallback']['CheckoutRequestID'];
            $ticket = Ticket::where('checkout_id', $checkout)->first();
            if($ticket){
                // User Cancelled
            }

        } else if($response['Body']['stkCallback']['ResultCode'] == 0){
            $item = $response['Body']['stkCallback']['CallbackMetadata']['Item'];
            $checkout = $response['Body']['stkCallback']['CheckoutRequestID'];
            $account = 'N/A';
            $ticket = Ticket::where('checkout_id', $checkout)->first();
            if($ticket){
                $account = $ticket->account;
            }
            $payer = 'N/A';
            $phone = collect($item)->where('Name','PhoneNumber')->first()['Value'];
            $mpesa_transaction_id = collect($item)->where('Name','MpesaReceiptNumber')->first()['Value'];
            $date_time = Carbon::parse(collect($item)->where('Name','TransactionDate')->first()['Value']);
            $amount = collect($item)->where('Name','Amount')->first()['Value'];
            $exists = Payment::where('transaction_number', $mpesa_transaction_id)->count();
            if($exists == 0){
                $payment = new Payment;
                $payment->transaction_number = $mpesa_transaction_id;
                $payment->transaction_date = $date_time;
                $payment->amount = $amount;
                $payment->account = strtoupper($account);
                $payment->name = $payer;
                $payment->phone = $phone;
                $payment->processed = 1;
                $payment->save();

                if($ticket){
                    $ticket->active = 1;
                    $ticket->save();
                }

            }
            $message = ["ResultCode" => 0, "ResultDesc" => "Success"];

            return response()->json($message);
        }
    }

}

