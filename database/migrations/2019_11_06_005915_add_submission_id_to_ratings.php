<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubmissionIdToRatings extends Migration
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

            Schema::table('ratings', function (Blueprint $table) {
                $table->unsignedInteger('dynamic_form_submission_id')
                    ->nullable()
                    ->comment('FK to dynamic_form_submissions if rating_system == form');

                $table->foreign('dynamic_form_submission_id')
                    ->references('id')
                    ->on('dynamic_form_submissions');
            });

            DB::commit();

        } catch (Exception $e) {
            DB::rollback();
            Log::error("Failed to migrate " . __FILE__);
            throw $e;
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

            Schema::table('ratings', function (Blueprint $table) {
				$table->dropForeign(['dynamic_form_submission_id']);
                $table->dropColumn('dynamic_form_submission_id');
			});

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            Log::error("Failed to rollback " . __FILE__);
            throw $e;
        }
    }
}
