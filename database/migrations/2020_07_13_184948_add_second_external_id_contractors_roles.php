<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSecondExternalIdContractorsRoles extends Migration
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

            Schema::table('roles', function (Blueprint $table) {
                if (Schema::hasColumn('roles', 'external_id')) {
                    $table->string('second_external_id')->after('external_id')->nullable();
                } else {
                    $table->string('second_external_id')->nullable();
                }
            });

            Schema::table('contractors', function (Blueprint $table) {
                if (Schema::hasColumn('contractors', 'external_id')) {
                    $table->string('second_external_id')->after('external_id')->nullable();
                } else {
                    $table->string('second_external_id')->nullable();
                }
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
        try {
            DB::beginTransaction();

            Schema::table('roles', function (Blueprint $table) {
                if (Schema::hasColumn('roles', 'second_external_id')) {
                    $table->dropColumn('second_external_id');
                } else {
                    Log::info("Column second_external_id not found in roles table, not dropped.");
                }
            });

            Schema::table('contractors', function (Blueprint $table) {
                if (Schema::hasColumn('contractors', 'second_external_id')) {
                    $table->dropColumn('second_external_id');
                } else {
                    Log::info("Column second_external_id not found in contractors table, not dropped.");
                }
            });

            DB::commit();


        } catch (Exception $ex) {
            DB::rollback();
            Log::debug(__METHOD__, ['exception' => $ex->getMessage()]);
        }
    }
}
