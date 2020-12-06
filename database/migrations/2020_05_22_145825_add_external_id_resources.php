<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AddExternalIdResources extends Migration
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
            if (!Schema::hasColumn('resources', 'external_id')) {
                Schema::table('resources', function (Blueprint $table) {
                    $table->string('external_id')->nullable()->comment('External ID used for 3rd Party API Integrations');
                });
            }
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
        try {
            DB::beginTransaction();
            if (Schema::hasColumn('resources', 'external_id')) {
                Schema::table('resources', function (Blueprint $table) {
                    $table->dropColumn('external_id');
                });
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::debug(__METHOD__, ['exception' => $ex->getMessage()]);
        }

    }
}
