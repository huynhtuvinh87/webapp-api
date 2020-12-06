<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRatingLabelHiringOrganization extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hiring_organizations', function (Blueprint $table) {
            $table->string('form_label_rating')->nullable()->comment("Form label to read rating score from");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hiring_organizations', function (Blueprint $table) {
            $table->dropColumn('form_label_rating');
        });
    }
}
