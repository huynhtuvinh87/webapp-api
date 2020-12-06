<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFlagPositionRolesTable extends Migration
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

            Schema::table('position_role', function (Blueprint $table) {
                if (Schema::hasColumn('position_role', 'role_id')) {
                    $table->integer('assigned_by_hiring_organization')->after('role_id')->default(0);
                } else {
                    $table->integer('assigned_by_hiring_organization')->default(0);
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

            Schema::table('position_role', function (Blueprint $table) {
                if (Schema::hasColumn('position_role', 'assigned_by_hiring_organization')) {
                    $table->dropColumn('assigned_by_hiring_organization');
                }
            });

            DB::commit();


        } catch (Exception $ex) {
            DB::rollback();
            Log::debug(__METHOD__, ['exception' => $ex->getMessage()]);
        }
    }
}
