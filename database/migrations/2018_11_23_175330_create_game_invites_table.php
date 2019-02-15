<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGameInvitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_invites', function (Blueprint $table) {
            $table->increments('id');
            $table->string('token')->unique();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedInteger('invitee_id');
            $table->foreign('invitee_id')->references('id')->on('users')->nullable();
            $table->unsignedInteger('game_id');
            $table->foreign('game_id')->references('id')->on('games');
            $table->boolean('is_owner')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_invites');
    }
}
