<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('postImages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('imageId')->unsigned();
            $table->integer('postId')->unsigned();
            $table->foreign('imageId')->references('id')->on('fileentries');
            $table->foreign('postId')->references('postId')->on('posts');
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
        Schema::dropIfExists('postImages');
    }
}
