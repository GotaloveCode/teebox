<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGameTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_game', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('game_id')->unsigned()->index();
            $table->foreign('game_id')->references('id')->on('games');
            $table->boolean('paid')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('user_game', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['game_id']);
        });
        Schema::dropIfExists('user_game');
    }
}
