<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBackupLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * Creating a backups table to store the various backups in the system
         */
        Schema::create('backups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('file_path');
            $table->string('environment');
            $table->timestamp('created_at')
                ->nullable();
            $table->timestamp('updated_at')
                ->nullable();
            $table->timestamp('last_verified_at')
                ->comment("Last time the file was verified")
                ->nullable();
            $table->timestamp('deleted_at')
                ->comment("aprox. time the file was deleted at")
                ->nullable();
        });

        Schema::dropIfExists('systems');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('backups');
        //
        Schema::create('systems', function (Blueprint $table) {
            $table->increments('id')
                ->comment("systems table is to handle generic information.");
        });
    }
}
