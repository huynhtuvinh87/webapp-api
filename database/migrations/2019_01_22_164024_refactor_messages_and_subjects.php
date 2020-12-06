<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RefactorMessagesAndSubjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('messages');
        Schema::drop('subjects');

        Schema::create('messages', function($table){
            $table->increments('id');
            $table->unsignedInteger('sender_id');
            $table->unsignedInteger('recipient_id');
            $table->text('message');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sender_id')->references('id')->on('users');
            $table->foreign('recipient_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
