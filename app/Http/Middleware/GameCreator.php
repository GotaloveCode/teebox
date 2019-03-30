<?php

namespace App\Http\Middleware;

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
        $game = $request->route('id');
        $user = Auth::user();

        if($game->created_by == $user->id){
            return $next($request);
        }

        return abort(403, 'Unauthorized');
    }
}
