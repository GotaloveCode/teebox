<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'website',
        'postal_address', 'physical_address','latlong'
    ];
    public function members(){
        return $this->belongsToMany('App/Club');
    }

    public function games(){
        return $this->hasMany('App/Games');
    }

    public function rates(){
        return $this->hasMany('App/Rate');
    }
}
