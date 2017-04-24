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
            $table->increments('id');
            $table->integer('userId')->unsigned();
            $table->string('location');
            $table->decimal('latitude', 10, 4);
            $table->decimal('longitude', 10, 4);
            $table->integer('numberOfRooms');
            $table->string('description');
            $table->integer('price')->nullable();
            $table->boolean('postType');
            $table->timestamps();
            $table->foreign('userId')->references('id')->on('users');
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
