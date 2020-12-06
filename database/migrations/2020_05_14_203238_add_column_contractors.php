<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AddColumnContractors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws Exception
     */
    public function up()
    {
        try {
            DB::beginTransaction();

            if (Schema::hasColumn('contractors', 'has_subcontractors')){
                Schema::table('contractors', function (Blueprint $table) {
                    $table->dropColumn('has_subcontractors');
                });
            }

            Schema::table('contractors', function (Blueprint $table) {
                $table->boolean('has_subcontractors')->default(0);
            });

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            Log::error("Failed to migrate " . __FILE__);

            if (config('app.env') != 'development') {
                throw new Exception($e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws Exception
     */
    public function down()
    {
        try {
            DB::beginTransaction();

            if (Schema::hasColumn('contractors', 'has_subcontractors')) {
                Schema::table('contractors', function (Blueprint $table) {
                    $table->dropColumn('has_subcontractors');
                });
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            Log::error("Failed to migrate " . __FILE__);

            if (config('app.env') != 'development') {
                throw new Exception($e->getMessage());
            }
        }
    }
}
