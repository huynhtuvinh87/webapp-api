<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDateDynamicFormType extends Migration
{
    public function up()
    {
        try {
            DB::beginTransaction();

            DB::statement("ALTER TABLE `dynamic_form_columns` CHANGE `type` `type` ENUM('label','text','numeric','checkbox','transformation','image','textarea','radio','select','date') NULL DEFAULT NULL");

            DB::commit();
        } catch (Exception $e) {
            Log::error($e);
            DB::rollback();
            throw $e;
        }
    }

    public function down()
    {
        try {
            DB::beginTransaction();
            DB::statement("UPDATE `dynamic_form_columns` SET `type` = 'text' WHERE `type` = 'date'");
            DB::statement("ALTER TABLE `dynamic_form_columns` CHANGE `type` `type` ENUM('label','text','numeric','checkbox','transformation','image','textarea','radio','select') NULL DEFAULT NULL");
            DB::commit();
        } catch (Exception $e) {
            Log::error($e);
            DB::rollback();
            throw $e;
        }
    }
}
