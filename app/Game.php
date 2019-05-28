<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $casts = ['active'=>'boolean'];

    protected $dates = ['start','end'];

    protected $fillable = ['type','start','end','club_id','account'];

    public function players(){
        return $this->belongsToMany(User::class)->withTimeStamps();
    }

    public function club(){
        return $this->belongsTo(Club::class);
    }

    public function invites()
    {
        return $this->hasMany(GameInvite::class, 'game_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    public function scopeConfirmedPlayers($query)
    {
        return $query->players()->where('paid', 1);
    }

    public function remainingPlayers()
    {
        return 4 - $this->players()->count();
    }

//    public function scopePayment($query,$status="paid")
//    {
//        $status == 1 ? "paid" : "unpaid";
//        return $query->where('paid', $status);
//    }

     /**
     * @param User $user
     * @param $game_id
     * @return Model|\Illuminate\Database\Query\Builder|null|object
     */
    public function add_invite(User $user, $game_id)
    {
        # first check if user is already a member of business
        $member =  $this->members()->newQuery()->where('users.id', $user->id)->first();
        if($member)
            return $member;

        # create member
        $member =  $this->members()->attach($user->id, [
            'game_id' => $game_id
        ]);

//        $user->notify(new BusinessMemberAdded($member));

        return $member;
    }
}
