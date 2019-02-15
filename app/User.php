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
        'first_name','other_names','email', 'password', 'phone', 'signup_platform',
        'email_verified_at', 'phone_verified_at','photo_url'
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
        return $this->belongsToMany(Club::Class)->withPivot('user_id','club_id','member_no');
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

    /**
     * Set the user's first name.
     *
     * @param  string  $value
     * @return void
     */
    public function setFirstNameAttributes($value){
        $this->attributes['first_name'] = ucfirst($value);
    }
    /**
     * Set the user's other names.
     *
     * @param  string  $value
     * @return void
     */
    public function setOtherNameAttributes($value){
        $this->attributes['other_names'] = ucwords($value);
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->other_names}";
    }

    /**
     * Encode phone number before saving
     * @param $value
     */
    public function setPhoneAttribute($value)
    {
        // only mutate if $value is not null
        $this->attributes['phone'] = !!$value ? phone($value, ['KE'], 'E164') : null;
    }

}
