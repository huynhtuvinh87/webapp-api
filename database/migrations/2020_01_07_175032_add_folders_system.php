<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFoldersSystem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        try {

            DB::beginTransaction();
            Schema::disableForeignKeyConstraints();

            // While working on migrations, the restore command doesn't automatically remove the tables. This ensures it is removed initially.
            Schema::dropIfExists('folders');
            Schema::create('folders', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->unsignedInteger('hiring_organization_id')->comment('FK hiring organization');
                $table->timestamps();
                $table->softDeletes();

                // Add index
                $table->foreign('hiring_organization_id')->references('id')->on('hiring_organizations');
            });

            Schema::dropIfExists('contractor_folder');
            Schema::create('contractor_folder', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('contractor_id')->comment('FK contractors');
                $table->unsignedBigInteger('folder_id')->comment('FK folders');
                $table->timestamps();

                // Add indexes
                $table->foreign('contractor_id')->references('id')->on('contractors')->onDelete('cascade');
                $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
            });

            Schema::dropIfExists('file_folder');
            Schema::create('file_folder', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('file_id')->comment('FK files');
                $table->unsignedBigInteger('folder_id')->comment('FK folders');
                $table->timestamps();

                // Add indexes
                $table->foreign('file_id')->references('id')->on('files');
                $table->foreign('folder_id')->references('id')->on('folders');
            });

            Schema::dropIfExists('department_folder');
            Schema::create('department_folder', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('department_id')->comment('FK departments');
                $table->unsignedBigInteger('folder_id')->comment('FK folders');
                $table->timestamps();

                // Add indexes
                $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
                $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
            });

        } catch (Exception $e) {
            Log::error($e);
            DB::rollback();
            throw $e;
        }

        Schema::enableForeignKeyConstraints();

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('department_folder');
        Schema::dropIfExists('file_folder');
        Schema::dropIfExists('contractor_folder');
        Schema::dropIfExists('folders');
        Schema::enableForeignKeyConstraints();
    }
}
