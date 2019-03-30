<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends BaseController
{
    public function index()
    {
        $user = Auth::user();
        $games = $user->games()->count();
        $memberships = $user->clubs()->count();
        return response()->json(['games' => $games,'memberships' => $memberships],200);
    }
}
