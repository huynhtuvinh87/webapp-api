<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddResourceIdToRequirementHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try { 
            Schema::table('requirement_histories', function (Blueprint $table) {
                $table->bigInteger('resource_id')->nullable()->index();
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
        Schema::table('resource_histories', function (Blueprint $table) {
            //
        });
    }
}
