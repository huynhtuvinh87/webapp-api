<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHardDeadlineDateToRequirementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requirements', function (Blueprint $table) {
            $table->date('hard_deadline_date')->comment("Date to override renewal date. Renewal date = hard_deadline_date when set.")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('requirements', 'hard_deadline_date')) {
            Schema::table('requirements', function (Blueprint $table) {
                $table->dropColumn('hard_deadline_date');
            });
        }
    }
}
