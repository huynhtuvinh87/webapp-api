<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequirementContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requirement_contents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('requirement_id');
            $table->string('lang')->default('en');
            $table->string('text')->nullable();
            $table->string('url')->nullable();
            $table->string('file')->nullable();
            $table->string('file_ext')->nullable();
            $table->string('file_name')->nullable();

            $table->foreign('requirement_id')->references('id')->on('requirements')->onDelete('cascade');

            $table->timestamps();
        });

        Schema::table('requirements', function(Blueprint $table){
            $table->dropColumn([
                'content',
                'content_file',
                'content_url',
                'content_file_name',
                'content_file_ext'
            ]);
            $table->enum('content_type', ['text', 'file', 'url'])->default('file');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('requirement_content');
    }
}
