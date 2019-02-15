<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameInvite extends Model
{
    public function users(){
        return $this->belongsToMany('App/User');
    }
    
    public function game()
    {
        return $this->belongsToMany('App/Game');
    }

    public function invited(){
        return $this->belongsTo(User::class, 'phone', 'phone');
    }

     public function accept(User $me)
    {
        $success = false;
        DB::beginTransaction();
        $game  = $this->game;
        $game->add_member($me, $this->loan_limit);
        $this->delete();
        activity()->log("{$me} accepted your game request");
        $success = true;
        DB::commit();

        return $success;
    }
}
