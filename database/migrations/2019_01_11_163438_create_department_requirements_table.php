<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepartmentRequirementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('department_requirements', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('department_id');
            $table->unsignedInteger('requirement_id');

            $table->foreign('department_id')->references('id')->on('departments');
            $table->foreign('requirement_id')->references('id')->on('requirements');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('department_requirements');
    }
}
