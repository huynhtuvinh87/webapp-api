<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVisibleRequirement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requirements', function (Blueprint $table) {
            $table->boolean('is_visible')->default(1)->comment('will the requirement be visible in front end');
            $table->boolean('can_edit')->default(1)->comment('ability for requirements to be set to not editable, if needed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requirements', function (Blueprint $table) {
            $table->dropColumn('is_visible');
            $table->dropColumn('can_edit');
        });
    }
}
