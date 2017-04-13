<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('postId');
            $table->integer('userId')->unsigned();
            $table->string('location');
            $table->integer('numberOfRooms');
            $table->boolean('type');
            $table->string('description');
            $table->integer('price');
            $table->boolean('postType');
            $table->timestamps();
            $table->foreign('userId')->references('userId')->on('users');
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
