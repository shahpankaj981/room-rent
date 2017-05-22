<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $table->increments('id');
            $table->integer('threadId')->unsigned();
            $table->integer('messageId')->unsigned();
            $table->integer('visibility');
            $table->foreign('threadId')->references('id')->on('threads')->onDelete('cascade');
            $table->foreign('messageId')->references('id')->on('messages')->onDelete('cascade');
            $table->timestamps();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('notifications');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
