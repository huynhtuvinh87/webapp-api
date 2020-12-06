<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('locks');
        Schema::create('locks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('entity_key', ['requirement_history']);
            $table->unsignedInteger('entity_id');
            $table->unsignedInteger('locker_role_id');
            $table->timestamps();
            $table->timestamp('ends_at')->nullable();
            $table->softDeletes();

            $table->foreign('locker_role_id')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('locks');
    }
}
