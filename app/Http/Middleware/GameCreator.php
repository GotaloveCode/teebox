<?php

namespace App\Http\Middleware;

use App\Game;
use Closure;
use Illuminate\Support\Facades\Auth;

class GameCreator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $game = $request->route('game');
        $user = Auth::user();
        if($game->players()->first()->id == $user->id){
            return $next($request);
        }
        return abort(403, 'Only the creator of this game may access this');
    }
}
