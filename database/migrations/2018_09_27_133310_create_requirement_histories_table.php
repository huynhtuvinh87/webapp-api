<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequirementHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requirement_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('requirement_id');
            $table->string('certificate_file')->nullable();
            $table->date('renewal_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->string('original_file_name')->nullable();
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
        Schema::dropIfExists('requirement_histories');
    }
}
