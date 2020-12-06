<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeCompletionDescriptionNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tasks', function($table){
            $table->text('completion_description')->nullable()->change();
            $table->unsignedInteger('approved_by')->nullable()->change();
            $table->datetime('completion_date')->nullable()->change();
            $table->datetime('approved_at')->nullable()->change();
            $table->boolean('is_approved')->default(0)->change();
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
