<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToContractorPositionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contractor_position', function (Blueprint $table) {
            //$table->foreign('contractor_id')->references('id')->on('contractors')->onDelete('cascade');
            //$table->foreign('position_id')->references('id')->on('positions')->onDelete('cascade');
            $table->index('contractor_id');
            $table->index('position_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contractor_position', function (Blueprint $table) {
            //
        });
    }
}
