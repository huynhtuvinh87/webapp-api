<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRequirementsHistoryLinkToDynamicFormSubmissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dynamic_form_submissions', function (Blueprint $table) {
            // Adding link to requirement submissions
            $table->unsignedInteger('requirement_history_id')
                ->comment("Links to requirement histories table")
                ->nullable();

            $table->foreign('requirement_history_id')
                ->references('id')
                ->on('requirement_histories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dynamic_form_submissions', function (Blueprint $table) {
            $table->dropColumn('requirement_history_id');
        });
    }
}
