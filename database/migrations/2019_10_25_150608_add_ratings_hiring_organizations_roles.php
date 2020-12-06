<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRatingsHiringOrganizationsRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::beginTransaction();

        try {

            Schema::table('ratings', function (Blueprint $table) {
                $table->dropForeign(['contractor_id']);
                $table->dropForeign(['hiring_organization_id']);
                $table->dropUnique(['contractor_id', 'hiring_organization_id']);
                $table->dropIndex('ratings_hiring_organization_id_foreign');
                $table->unsignedInteger('role_id')->nullable()->comment('FK roles');
                $table->foreign('role_id')->references('id')->on('roles');
            });

            Schema::table('hiring_organizations', function (Blueprint $table) {
                $table->enum('rating_system', ['star', 'form'])->default('star')->comment("Type of rating used in the Hiring Organization");
                $table->enum('rating_visibility', ['public', 'private'])->default('public')->comment("Visibility of rating internally for Hiring Organization");
                $table->unsignedInteger('form_rating_requirement_id')->nullable()->comment("FK requirements");
                $table->foreign('form_rating_requirement_id')->references('id')->on('requirements');
            });

            Schema::table('roles', function (Blueprint $table) {
                $table->boolean('can_create_rating')->default(1);
            });

            Schema::create('rating_requirement_history', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('rating_id')->comment('FK ratings');
                $table->unsignedInteger('requirement_history_id')->comment('FK to requirement hitories');
                $table->foreign('rating_id')->references('id')->on('ratings');
                $table->foreign('requirement_history_id')->references('id')->on('requirement_histories');
            });

            DB::commit();

        } catch (Exception $e) {

            Log::error($e->getMessage());
            DB::rollback();

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropForeign('ratings_role_id_foreign');
            $table->dropColumn('role_id');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('can_create_rating');
        });

        Schema::table('hiring_organizations', function (Blueprint $table) {
            $table->dropForeign('hiring_organizations_form_rating_requirement_id_foreign');
            $table->dropColumn('rating_system');
            $table->dropColumn('rating_visibility');
            $table->dropColumn('form_rating_requirement_id');
        });

        Schema::drop('rating_requirement_history');
        Schema::enableForeignKeyConstraints();
    }
}
