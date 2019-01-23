<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Code extends Model
{

    protected $fillable = [
        'code_email', 'email','phone'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
