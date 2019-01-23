<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = ['type','start','end','club_id'];

    public function players(){
        return $this->belongsToMany('App/User');
    }

    public function club(){
        return $this->belongsTo('App/Club');
    }
}
