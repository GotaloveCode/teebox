<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCodesTable extends Migration
{
    public function up()
    {
        Schema::create('codes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code_email');
            $table->string('email')->nullable();
            $table->string('code_phone')->nullable();
            $table->string('phone')->nullable();
            $table->unsignedInteger('user_id')->nullable()->unique();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('count')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('codes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('codes');
    }
}
