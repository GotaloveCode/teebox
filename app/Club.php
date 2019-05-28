<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'website',
        'postal_address', 'physical_address','latlong','photo_url'
    ];
    public function users(){
        return $this->belongsToMany(User::Class)->withPivot('user_id','member_no');
    }

    public function games(){
        return $this->hasMany(Game::class);
    }

    public function rates(){
        return $this->hasMany(Rate::Class);
    }

    public function paymentModes(){
        return $this->hasMany(PaymentMode::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|\Illuminate\Database\Query\Builder
     */
    public function membersRate()
    {
        return $this->hasMany(Rate::class)
        ->select('amount')
        ->where('is_member',1)
        ->first()->amount;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|\Illuminate\Database\Query\Builder
     */
    public function nonMembersRate()
    {
        return $this->hasMany(Rate::class)
        ->select('amount')
        ->where('is_member',0)
        ->first()->amount;
    }

    public function getPhotoUrlAttribute($value){
        $default_avatar = "img/business-avatar.png";
        return $value ? url($value) :env('APP_URL')."/".($default_avatar);
    }
}
