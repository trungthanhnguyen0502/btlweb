<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('it_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 255);
            $table->string('email', 255);
            $table->string('password');
            $table->integer('birthday');
            $table->tinyInteger('gender');
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
        Schema::dropIfExists('it_users');
    }
}
