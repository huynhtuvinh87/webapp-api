<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDynamicFormContext extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('dynamic_forms', function (Blueprint $table) {
            // Adding link to requirement submissions
            // NOTE: If this line breaks, need to remove the existing forms (if they're all test forms),
            // OR, add a line that grabs the user's hiring org based on the `creator_role_id` column
            $table->unsignedInteger('hiring_organization_id')
                ->comment("hiring organization ID that created the form");

            $table->foreign('hiring_organization_id')
                ->references('id')
                ->on('hiring_organizations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dynamic_forms', function (Blueprint $table) {
            $table->dropColumn('hiring_organization_id');
        });
    }
}
