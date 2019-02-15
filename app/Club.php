<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'website',
        'postal_address', 'physical_address','latlong'
    ];
    public function users(){
        return $this->belongsToMany(User::Class)->withPivot('user_id','member_no');
    }

    public function games(){
        return $this->hasMany('App/Games');
    }

    public function rates(){
        return $this->hasMany('App/Rate');
    }
}
