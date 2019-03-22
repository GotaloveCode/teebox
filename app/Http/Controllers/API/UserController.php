<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Club;

class UserController extends Controller
{
     public function registerClub(Request $request){
         $this->validate($request, [
             'member_no' => "required",
             'club_id' => "required|exists:clubs,id"
         ]);

        $user = auth()->user();

        $exists = $user->clubs()->where('club_id',$request->club_id)->first();

        if($exists){
            return response()->json(['message' => $user->first_name.' already registered in '.$exists->name],422);
        }

        $user->clubs()->attach($request->club_id,['member_no' => $request->member_no]);

        return response()->json(['message' => $user->first_name.' club registration successful']);
    }

    
}
