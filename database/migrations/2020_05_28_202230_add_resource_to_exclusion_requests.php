<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddResourceToExclusionRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        try {
            DB::beginTransaction();

            if (Schema::hasColumn('exclusion_requests', 'resource_id')) {
                Schema::table('exclusion_requests', function (Blueprint $table) {
                    $table->dropColumn('resource_id');
                });
            }

            Schema::table('exclusion_requests', function (Blueprint $table) {
                $table->boolean('resource_id')->nullable();
            });

            DB::commit();

        } catch (Exception $ex) {
            DB::rollback();
            Log::debug(__METHOD__, ['exception' => $ex->getMessage()]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('exclusion_requests', 'resource_id')) {
            Schema::table('exclusion_requests', function (Blueprint $table) {
                $table->dropColumn('resource_id');
            });
        }
    }
}
