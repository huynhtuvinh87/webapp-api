<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SchemaRevisionsRound1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contractors', function($table){
            $table->dropColumn('kpi_overall_compliance');
        });

        Schema::table('contractors', function($table){
            $table->dropColumn('kpi_completed_requirements');
        });

        Schema::table('contractors', function($table){
            $table->dropColumn('kpi_pending_requirements');
        });

        Schema::table('contractors', function($table){
            $table->dropColumn('kpi_declined_requirements');
        });

        Schema::table('contractors', function($table){
            $table->dropColumn('vendor_number');
        });

        Schema::table('exclusion_requests', function($table){
            $table->unsignedInteger('requester_role_id')->nullable();
            $table->unsignedInteger('response_role_id')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->text('requester_note')->nullable();
            $table->text('responder_note')->nullable();
            $table->dropColumn('note');
        });

        Schema::table('positions', function($table){
            $table->dropColumn('role_id');
            $table->string('position_type')->default('employee');
        });

        Schema::table('requirement_histories', function($table){
            $table->dropColumn('renewal_date');
            $table->timestamp('due_date')->nullable();
            $table->unsignedInteger('role_id')->nullable();
        });

        Schema::table('requirements', function($table){
            $table->dropColumn('completion_date');
        });

        Schema::table('requirements', function($table){
            $table->dropColumn('renewal_date');
        });

        Schema::table('requirements', function($table){
            $table->dropColumn('completed');
        });

        Schema::table('requirements', function($table){
            $table->dropColumn('approvement_status');
        });

        Schema::table('requirements', function($table){
            $table->dropColumn('certificate_file');
        });

        Schema::create('requirement_history_reviews', function($table){
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('requirement_histories_id');
            $table->unsignedInteger('approver_id');
            $table->text('notes')->nullable();
            $table->string('status');
            $table->timestamp('status_at')->nullable();
        });

        Schema::rename('contractor_hiring_organization', 'contractor_hiring_organizations');

        Schema::table('contractor_hiring_organizations', function($table){
             $table->dropColumn('facility_id');
        });


        Schema::table('contractor_hiring_organizations', function($table){
            $table->integer('vendor_number')->nullable();
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
