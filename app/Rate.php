<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    protected $fillable = ['is_member','amount','club_id'];

    public function clubs(){
        return $this->belongsToMany('App/Club');
    }
}
