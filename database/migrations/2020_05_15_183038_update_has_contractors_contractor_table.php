<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class UpdateHasContractorsContractorTable extends Migration
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

            if (Schema::hasColumn('contractors', 'has_subcontractors')) {
                DB::table('contractors')
                    ->where('created_at', '>', Carbon::parse(config('api.subcontractor_survey.roles_registered_after')))
                    ->update(['has_subcontractors' => true]);
            } else {
                throw new Exception("Column has_subcontractors not found. ");
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
                DB::table('contractors')->update(['has_subcontractors' => false]);
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
