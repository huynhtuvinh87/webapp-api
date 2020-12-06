<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAcceptedColumnToContractorHiringOrganizationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contractor_hiring_organization', function (Blueprint $table) {
            $table->tinyInteger('accepted')->default(1);
            $table->index('accepted');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contractor_hiring_organization', function (Blueprint $table) {
            //
        });
    }
}
