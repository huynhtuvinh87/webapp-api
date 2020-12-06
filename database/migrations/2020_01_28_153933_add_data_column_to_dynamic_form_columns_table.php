<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDataColumnToDynamicFormColumnsTable extends Migration
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

            Schema::table('dynamic_form_columns', function (Blueprint $table) {

                // Dropping column if it already exists
                if (Schema::hasColumn('dynamic_form_columns', 'data')) {
                    Schema::table('dynamic_form_columns', function (Blueprint $table) {
                        $table->dropColumn('data');
                    });
                }

                $table->text('data')->nullable();

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
     */
    public function down()
    {
        Schema::table('dynamic_form_columns', function (Blueprint $table) {
            //
        });
    }
}
