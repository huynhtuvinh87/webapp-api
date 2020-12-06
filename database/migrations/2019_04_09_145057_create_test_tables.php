<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestTables extends Migration
{
    /**
     * Run the migrations.
     * TODO: IMPORTANT - why aren't foreign keys working?
     * @return void
     */
    public function up()
    {
        Schema::create('tests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('displayCount')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->longText('html');
            $table->integer('max_tries')->default(25);
            $table->integer('min_passing_criteria')->default(80);

           // $table->foreign('created_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('test_id');
            $table->string('question_text');
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->tinyInteger('isActive')->default(1);

            $table->string('option_1')->nullable();
            $table->string('option_2')->nullable();
            $table->string('option_3')->nullable();
            $table->string('option_4')->nullable();
            $table->string('correct_answer');

            //$table->foreign('test_id')->references('id')->on('tests');
            //$table->foreign('created_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('answers', function (Blueprint $table){
             $table->increments('id');
             $table->unsignedBigInteger('requirement_history_id');
             $table->unsignedBigInteger('question_id');
             $table->string('answer_text');
             $table->tinyInteger('correct_answer');

             //$table->foreign('requirement_history_id')->references('id')->on('requirement_histories');
             //$table->foreign('question_id')->references('id')->on('questions');
             $table->timestamps();
        });

        Schema::table('requirements', function($table){
            $table->unsignedInteger('test_id')->nullable();
            //$table->foreign('test_id')->references('id')->on('tests');
        });

        Schema::table('requirement_histories', function(Blueprint $table){
            $table->tinyInteger('valid')->default(1);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test_tables');
    }
}
