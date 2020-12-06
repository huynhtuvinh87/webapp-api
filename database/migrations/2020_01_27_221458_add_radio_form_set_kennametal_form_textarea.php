<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AddRadioFormSetKennametalFormTextarea extends Migration
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

            DB::statement("ALTER TABLE `dynamic_form_columns` CHANGE `type` `type` ENUM('label','text','numeric','checkbox','transformation','image','textarea', 'radio') NULL DEFAULT NULL");

            DB::statement("DELETE FROM dynamic_form_columns WHERE (label LIKE '6a.%' OR label LIKE '8a.%') AND dynamic_form_id = 19");

            DB::statement("UPDATE dynamic_form_columns SET label = '6a. Please fill this out in case you selected \'Yes\' in the question 6.', description = '', `type` = 'label', `order` = 24 WHERE id=2624");

            DB::statement("UPDATE dynamic_form_columns SET label = '8a. Please fill this out in case you selected \'Yes\' in the question 8.', description = '', `type` = 'label', `order` = 34 WHERE id=2633");

            try{

                DB::statement("INSERT INTO dynamic_form_columns SET dynamic_form_id = 19, label = 'Has altering/debilitating or fatal incidents within the last 5 years', description = 'Please provide a description of work being performed at the time, events that occurred, cause(s) and corrective actions taken:', `type` = 'textarea', `order` = 24");

                DB::statement("INSERT INTO dynamic_form_columns SET dynamic_form_id = 19, label = 'Has receive any serious, repeat or criminal citations for environmental, health or safety.', description = 'Please provide a description of work being performed at the time, events that occurred, cause(s) and corrective actions taken:', `type` = 'textarea', `order` = 34");
            } catch (Exception $e){
                Log::error(__METHOD__, [
                    'message' => 'Could not complete transaction - could be due to a fresh migration',
                    'error' => $e->getMessage(),
                ]);
            }

            DB::commit();
        } catch (Exception $e) {
            Log::error($e);
            DB::rollback();
            throw $e;
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
            DB::statement("UPDATE `dynamic_form_columns` SET `type` = NULL WHERE `type` = 'radio'");
            DB::statement("ALTER TABLE `dynamic_form_columns` CHANGE `type` `type` ENUM('label','text','numeric','checkbox','transformation','image','textarea') NULL DEFAULT NULL");
            DB::commit();
        } catch (Exception $e) {
            Log::error($e);
            DB::rollback();
            throw $e;
        }
    }
}
