<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AddInheritToModulesTable extends Migration
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
                Schema::table('modules', function (Blueprint $table) {
                    $table->boolean('inherit')->default(0)->comment("Children entities will automatically inherit this module");
                });
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();

            Log::error(__METHOD__, ['exception' => $exception->getMessage()]);
            throw new Error("Failed to migrate");
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
                if (Schema::hasColumn('modules', 'inherit')) {
                    Schema::table('modules', function (Blueprint $table) {
                        $table->dropColumn('inherit');
                    });
                }
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();

            Log::error(__METHOD__, ['exception' => $exception->getMessage()]);
            throw new Error("Failed to rollback");
        }
    }
}
