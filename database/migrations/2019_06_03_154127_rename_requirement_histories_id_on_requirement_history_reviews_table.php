<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameRequirementHistoriesIdOnRequirementHistoryReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requirement_history_reviews', function(Blueprint $table){
            $table->dropIndex('req_histories_id');
            $table->dropColumn('requirement_histories_id');

            $table->unsignedBigInteger('requirement_history_id');
            $table->index('requirement_history_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
