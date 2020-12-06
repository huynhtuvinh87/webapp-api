<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToThings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requirement_histories', function (Blueprint $table) {
            $table->index(['requirement_id', 'contractor_id', 'completion_date'], 'req_id_contractor_id');
            $table->index(['requirement_id', 'role_id'], 'req_id_role_id');
        });

        Schema::table('requirement_history_reviews', function(Blueprint $table){
            $table->index('requirement_histories_id', 'req_histories_id');
        });

        Schema::table('exclusion_requests', function(Blueprint $table){
            $table->index(['requirement_id', 'requester_role_id'], 'requirement_requestor');
        });

        Schema::table('position_requirement', function(Blueprint $table){
            $table->index('position_id', 'position_id');
        });

        Schema::table('position_role', function(Blueprint $table){
            $table->index('position_id', 'position_role_position_id_foreign');
            $table->index('role_id', 'position_role_role_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('things', function (Blueprint $table) {
            //
        });
    }
}
