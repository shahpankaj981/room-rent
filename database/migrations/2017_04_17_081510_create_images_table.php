<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->increments('id');
            $table->string('filename');
            $table->string('mime');
            $table->integer('userId')->unsigned();
            $table->integer('postId')->unsigned();
            $table->string('original_filename');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('userId')->references('userId')->on('users');
            $table->foreign('postId')->references('postId')->on('posts');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('images');
    }
}
