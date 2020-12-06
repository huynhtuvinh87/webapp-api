<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeRatingValueLabelToId extends Migration
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

            DB::statement("ALTER TABLE hiring_organizations
			CHANGE form_label_rating dynamic_form_column_id_rating varchar(255) NULL");
            DB::statement("ALTER TABLE hiring_organizations
			MODIFY COLUMN dynamic_form_column_id_rating INT UNSIGNED NULL
			COMMENT 'Form column ID to read rating score from'");

            Schema::table('hiring_organizations', function (Blueprint $table) {
                $table->foreign('dynamic_form_column_id_rating')
                    ->references('id')
                    ->on('dynamic_form_columns');
            });

            DB::commit();
        } catch (Exception $e) {
            Log::error("Error trying to migrate", ['message' => $e->getMessage()]);
            DB::rollback();
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

            DB::statement("ALTER TABLE hiring_organizations
			CHANGE dynamic_form_column_id_rating form_label_rating varchar(255) NULL");

            DB::commit();
        } catch (Exception $e) {
            Log::error("Error trying to rollback", ['message' => $e->getMessage()]);
            DB::rollback();
            throw $e;
        }
    }
}
