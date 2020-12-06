<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFileFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contractors', function($table){
            $table->string('logo')->nullable(); // eg /path/to/file.png
            $table->string('logo_file_name')->nullable();
            $table->string('logo_file_ext')->nullable();
        });

        Schema::table('hiring_organizations', function($table){
            $table->string('logo')->nullable(); // eg /path/to/file.png
            $table->string('logo_file_name')->nullable();
            $table->string('logo_file_ext')->nullable();
        });

        Schema::table('task_attachments', function($table){
            $table->string('file')->nullable();
            $table->string('file_ext')->nullable();
        });

        Schema::table('users', function($table){
            $table->string('avatar_file_name')->nullable();
            $table->string('avatar_file_ext')->nullable();
        });
        
        Schema::table('requirement_histories', function($table){
            $table->string('certificate_file_name')->nullable();
            $table->string('certificate_file_ext')->nullable();
        });
        
        Schema::table('requirements', function($table){
            $table->string('content_file_name')->nullable();
            $table->string('content_file_ext')->nullable();
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
