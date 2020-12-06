<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResourcePositionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::create('resource_position', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('resource_id')->index();
                $table->bigInteger('position_id')->index();
                $table->timestamps();
            });
        }
        catch(Exception $ex) {

        }
       
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resource_position');
    }
}
