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
             'club_id' => "required|exists:clubs,id",
             'user_id' => "required|exists:users,id",
             ]);
        $user = User::WhereId($request->user_id);

        $user->clubs()->attach($request->club_id);

        return response()->json(['message' => $user->name.' club registration successful']);
    }

    
}
