<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('userId');
            $table->string('email')->unique();
            $table->string('userName')->unique();
            $table->string('name');
            $table->string('password');
            $table->string('phone');
            $table->string('profileImage')->default('default.jpeg');
            $table->boolean('activation');
            $table->string('confirmationCode');
            $table->string('forgotPasswordToken');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
