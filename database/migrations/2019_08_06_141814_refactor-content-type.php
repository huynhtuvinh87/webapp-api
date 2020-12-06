<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RefactorContentType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requirement_contents', function(Blueprint $table){
            $table->string('name')->nullable();
            $table->longText('description')->nullable();
        });

        Schema::table('requirements', function(Blueprint $table){
            $table->dropColumn('name');
        });

        \App\Models\Requirement::whereNotNull('id')->delete();
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
