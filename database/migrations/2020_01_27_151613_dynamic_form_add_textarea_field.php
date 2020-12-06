<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DynamicFormAddTextareaField extends Migration
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

            DB::statement("ALTER TABLE `dynamic_form_columns` CHANGE `type` `type` ENUM('label','text','numeric','checkbox','transformation','image','textarea') NULL DEFAULT NULL");

            Schema::table('roles', function (Blueprint $table) {
                // Dropping column if it already exists
                if (Schema::hasColumn('roles', 'can_invite_contractor')) {
                    Schema::table('roles', function (Blueprint $table) {
                        $table->dropColumn('can_invite_contractor');
                    });
                }

                $table->boolean('can_invite_contractor')->default(1)->comment("Used to display/hide 'Add contractor' btn");

            });

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

            DB::statement("UPDATE `dynamic_form_columns` SET `type` = NULL WHERE `type` = 'textarea'");
            DB::statement("ALTER TABLE `dynamic_form_columns` CHANGE `type` `type` ENUM('label','text','numeric','checkbox','transformation','image') NULL DEFAULT NULL");

            if (Schema::hasColumn('roles', 'can_invite_contractor')) {
                Schema::table('roles', function (Blueprint $table) {
                    $table->dropColumn('can_invite_contractor');
                });
            }
            DB::commit();
        } catch (Exception $e) {
            Log::error($e);
            DB::rollback();
            throw $e;
        }

    }
}
