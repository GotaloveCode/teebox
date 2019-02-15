<?php

use Illuminate\Database\Seeder;
use App\Club;
use App\User;

class UserClubSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::all();
        $club = Club::all();
        //create membership
        for ($i = 0; $i < $user->count(); $i++ ){
            $club[$i]->users()->attach($user[$i]->id,['member_no' => rand(2323,89723)]);
        }
    }
}
