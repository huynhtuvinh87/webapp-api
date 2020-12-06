<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->enum('role', ['employee', 'owner', 'admin']);
            $table->enum('entity_key', ['contractor', 'hiring_organization']);
            $table->integer('entity_id');
            $table->integer('pivot_contractor_id')->nullable();
            $table->string('employee_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     *
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
}
