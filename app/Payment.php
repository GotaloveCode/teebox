<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['transaction_number','transaction_date','amount','account','phone','name','source','status'];
}
