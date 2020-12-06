<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeLocationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function(Blueprint $table){
            $table->increments('id');
            $table->string('sortname');
            $table->string('name');
            $table->string('phonecode');
        });

        Schema::create('states', function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('country_id');
            $table->string('sortname');

            $table->foreign('country_id')->references('id')->on('countries');
        });

        Schema::create('tax', function(Blueprint $table){
            $table->increments('id');
            $table->string('province_name');
            $table->unsignedInteger('state_id');
            $table->unsignedInteger('country_id');
            $table->string('abbreviation');
            $table->integer('tax_rate');
            $table->string('tax_type');

            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('state_id')->references('id')->on('states');
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
