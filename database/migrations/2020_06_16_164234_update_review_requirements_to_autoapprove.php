<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateReviewRequirementsToAutoapprove extends Migration
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
            DB::statement("UPDATE requirements SET count_if_not_approved = 1 WHERE `type` = 'review' AND count_if_not_approved = 0");
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e);
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
