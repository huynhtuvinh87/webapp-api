<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddImageTypeToDynamicForms extends Migration
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

            DB::statement("ALTER TABLE `dynamic_form_columns` CHANGE `type` `type` ENUM('label','text','numeric','checkbox','transformation','image') NULL DEFAULT NULL");
            Schema::table('dynamic_form_columns', function (Blueprint $table) {
                // Dropping column if it already exists
                if (Schema::hasColumn('dynamic_form_columns', 'file_id')) {
                    Schema::table('dynamic_form_columns', function (Blueprint $table) {
                        $table->dropColumn('file_id');
                    });
                }

                $table->unsignedBigInteger('file_id')->nullable();
                $table->foreign('file_id')->references('id')->on('files')->onDelete('set null');

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
            DB::statement("ALTER TABLE `dynamic_form_columns` CHANGE `type` `type` ENUM('label','text','numeric','checkbox','transformation') NULL DEFAULT NULL");


            if (Schema::hasColumn('dynamic_form_columns', 'file_id')) {
                Schema::table('dynamic_form_columns', function (Blueprint $table) {

                    $table->dropForeign(['file_id']);
                    $table->dropColumn('file_id');
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
