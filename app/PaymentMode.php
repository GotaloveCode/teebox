<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentMode extends Model
{
    protected $fillable = ['name','account','description'];

    public function club(){
        return $this->belongsTo(Club::Class);
    }
}
