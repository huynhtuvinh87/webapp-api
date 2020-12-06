<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SchemaRevisionsRound3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hiring_organizations', function($table){
            $table->tinyInteger('is_active')->default(1);
            $table->string('country')->nullable();
        });

        Schema::table('requirements', function($table){
            $table->unsignedInteger('hiring_organization_id');
            $table->foreign('hiring_organization_id')->references('id')->on('hiring_organizations');
        });

        Schema::table('users', function($table){
            $table->tinyInteger('use_previewer')->default(1);
        });

        Schema::table('positions', function($table){
            $table->tinyInteger('is_active')->default(1);
        });

        Schema::table('contractors', function($table){
            $table->string('postal_code')->nullable();
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
