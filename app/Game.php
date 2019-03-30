<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = ['type','start','end','club_id'];

    public function players(){
        return $this->belongsToMany(User::class);
    }

    public function club(){
        return $this->belongsTo(Club::class);
    }

    public function invites()
    {
        return $this->hasMany(GameInvite::class, 'game_id');
    }

     /**
     * @param User $user
     * @param $loan_limit
     * @return Model|\Illuminate\Database\Query\Builder|null|object
     */
    public function add_member(User $user, $game_id = null)
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
