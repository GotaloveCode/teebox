<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rates', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('is_member')->default(true);
            $table->float('amount', 8, 2);
            $table->integer('club_id')->unsigned()->index();
            $table->foreign('club_id')->references('id')->on('clubs');
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::table('rates', function (Blueprint $table) {
            $table->dropForeign(['club_id']);
        });
        Schema::dropIfExists('rates');
    }
}
