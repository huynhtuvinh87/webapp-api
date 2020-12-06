<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('short_description');
            $table->text('long_description');
            $table->text('completion_description');
            $table->string('status')->default('open');

            $table->unsignedInteger('assigned_by');
            $table->unsignedInteger('assigned_to');
            $table->unsignedInteger('approved_by');
            $table->unsignedInteger('task_type_id');

            $table->dateTime('target_date');
            $table->dateTime('completion_date');
            $table->dateTime('approved_at');
            $table->timestamps();

            $table->boolean('is_approved')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
