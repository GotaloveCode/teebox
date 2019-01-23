<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('account');
            $table->text('description')->nullable();
            $table->string('status')->nullable();
            $table->decimal('amount', 8, 2);
            $table->unsignedInteger('payment_mode_id');
            $table->foreign('payment_mode_id')->references('id')->on('payment_modes');
            $table->unsignedInteger('game_id');
            $table->foreign('game_id')->references('id')->on('games');

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
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['game_id']);
            $table->dropForeign(['payment_mode_id']);
        });
        Schema::dropIfExists('payments');
    }
}
