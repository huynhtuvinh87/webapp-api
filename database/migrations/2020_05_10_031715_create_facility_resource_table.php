<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CreateFacilityResourceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();
        try {
            Schema::create('facility_resource', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('resource_id')->index();
                $table->integer('facility_id')->index();
                $table->timestamps();

                DB::commit();
            });
        } catch (Exception $ex) {
            Log::error(__METHOD__);
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
            Schema::dropIfExists('facility_resource');
        } catch (Exception $ex) {
            DB::rollback();
            Log::error("Failed to drop resources");
            Log::error($ex->getMessage());
            Log::debug($ex);
        }
    }
}
