<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileModel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            // While working on migrations, the restore command doesn't automatically remove the tables. This ensures it is removed initially.
            Schema::dropIfExists('files');

            DB::beginTransaction();


            // Creating files table
            Schema::create('files', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->longText("name")
                    ->nullable()
                    ->comment("Original file name");
                $table->longText('path')
                    ->comment("Path to be provided to access the file on the server");
                $table->string('ext')
                    ->nullable()
                    ->comment("File extension");
                $table->unsignedInteger('role_id')
                    ->nullable()
                    ->comment("Role that uploaded the file");
                $table->string("ip")
                    ->nullable()
                    ->comment("IP that the file was uploaded from");
                $table->string('disk')
                    ->nullable()
                    ->comment("disk the file is stored in");
                $table->string("visibility")
                    ->nullable()
                    ->comment("Visibility of the file: public or private (not using enum as Laravel doesn't handle them well)");
                $table->timestamp('updated_at')
                    ->useCurrent();
                $table->timestamp('created_at')
                    ->useCurrent();
                $table->timestamp('deleted_at')
                    ->nullable();
            });

            DB::commit();
        } catch (Exception $e) {
            Log::error($e);
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
}
