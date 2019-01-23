<?php

namespace App;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject,MustVerifyEmail
{
    use Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
//    'first_name', 'last_name', 'other_name',
    protected $fillable = [
        'name','email', 'password', 'phone', 'signup_platform',
        'email_verified_at', 'phone_verified_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function clubs(){
        return $this->belongsToMany('App/Club');
    }

    public function games(){
        return $this->belongsToMany('App/Games');
    }

    public function invites(){
        return $this->hasMany('App/Invite');
    }

    public function code()
    {
        return $this->hasOne('App\Code');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

}
