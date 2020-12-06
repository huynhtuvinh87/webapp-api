<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AddDisplayOnRegisterColumnToFacilitiesTable extends Migration
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

            if (!Schema::hasColumn('modules', 'display_on_registration')) {
                Schema::table('facilities', function (Blueprint $table) {
                    $table->boolean('display_on_registration')
                        ->after('notification_email')
                        ->default(1)
                        ->comment("To be displayed on contractor registration page to be selected");
                });
            }

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

            if (Schema::hasColumn('facilities', 'display_on_registration')) {
                Schema::table('facilities', function (Blueprint $table) {
                    $table->dropColumn('display_on_registration');
                });
            }

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();

            Log::error(__METHOD__, ['exception' => $exception->getMessage()]);
            throw new Error("Failed to migrate");
        }
    }
}
