<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModuleFiltrationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')
                ->comment('module name, snake case (underscores)');
            $table->boolean('visible')
                ->comment('default visibility');
        });

        Schema::create('module_visibilities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('module_id');
            $table->enum('entity_type', ['hiring_organization', 'contractor', 'role'])
                ->comment("Polymorphic Key - Points to hiring organization, contractor, or role");
            $table->unsignedInteger('entity_id');
            $table->boolean('visible')
                ->comment('overrided visibility');
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
        Schema::dropIfExists('modules');
        Schema::dropIfExists('module_visibility');
        Schema::dropIfExists('module_visibilities');
    }
}
