<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequiredColumnToDynamicFormColumnsTable extends Migration
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
                if (Schema::hasColumn('dynamic_form_columns', 'required')) {
                    Schema::table('dynamic_form_columns', function (Blueprint $table) {
                        $table->dropColumn('required');
                    });
                }

                $table->boolean('required')->default(false);

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
            // Dropping column if it already exists
            if (Schema::hasColumn('dynamic_form_columns', 'required')) {
                Schema::table('dynamic_form_columns', function (Blueprint $table) {
                    $table->dropColumn('required');
                });
            }
        });
    }
}
