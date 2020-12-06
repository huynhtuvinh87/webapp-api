<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::create('resources', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('contractor_id')->index();
                $table->string('name');
                $table->timestamps();
            });
        }
        catch(Exception $ex) {
            Log::error("Failed to migrate");
            Log::error($ex->getMessage());
            Log::debug($ex);
        }
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        try {
            Schema::dropIfExists('resources');
        }
        catch(Exception $ex) {
            Log::error("Failed to drop resources");
            Log::error($ex->getMessage());
            Log::debug($ex);
        }
    }
}
