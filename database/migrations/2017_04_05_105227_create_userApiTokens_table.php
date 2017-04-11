<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserApiTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('userApiTokens', function (Blueprint $table) {
            $table->increments('Id');
            $table->integer('userId');//->references('userId')->on('users');
            $table->integer('deviceType');
            $table->string('userApiToken');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('userApiTokens');
    }
}
