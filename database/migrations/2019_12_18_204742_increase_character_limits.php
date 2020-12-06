<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncreaseCharacterLimits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `contractors` DROP COLUMN `registration_deadline`');

        Schema::table('contractors', function (Blueprint $table) {
            $table->text('name')->change();
        });
        Schema::table('facilities', function (Blueprint $table) {
            $table->text('name')->change();
            $table->longText('description')->change();
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->text('name')->change();
        });

        Schema::table('requirement_contents', function (Blueprint $table){
            $table->text('name')->nullable()->change();
        });

        Schema::table('dynamic_forms', function (Blueprint $table) {
            $table->text('title')->change();
            $table->longText('description')->nullable()->change();
        });

        Schema::table('dynamic_form_submission_data', function (Blueprint $table) {
            $table->longText('dynamic_form_column_label')->change();
            $table->longText('value')->change();
        });

        Schema::table('tests', function (Blueprint $table) {
            $table->text('name')->change();
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->text('option_1')->change();
            $table->text('option_2')->change();
            $table->text('option_3')->change();
            $table->text('option_4')->change();
            $table->text('correct_answer')->change();
        });

        Schema::table('tests',
            function (Blueprint $table) {
                $table->dateTime('updated_at')->useCurrent()->nullable()->change();
                $table->text('name')->change();
            });

        Schema::table('contractors', function (Blueprint $table) {
            $table->text('name')->change();
//            $table->date('registration_deadline')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // It will not shrink the column back to what it was, since it will truncate data.
    }
}
