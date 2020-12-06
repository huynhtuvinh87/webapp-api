<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractorFacilityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contractor_facility', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('contractor_id');
            $table->unsignedInteger('facility_id');

            $table->foreign('contractor_id')->references('id')->on('contractors');
            $table->foreign('facility_id')->references('id')->on('facilities');

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
        Schema::dropIfExists('contractor_facility');
    }
}
