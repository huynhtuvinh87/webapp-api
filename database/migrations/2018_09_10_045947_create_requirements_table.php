<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequirementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requirements', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('uploader_id');
            $table->string('name');
            $table->date('due_date');
            $table->date('completion_date')->nullable();
            $table->date('renewal_date');
            $table->integer('warning_period')->default(30);
            $table->integer('renewal_period')->default(10);
            $table->enum('status', ['on_time', 'past_due', 'in_warning'])->nullable();
            $table->enum('type', ['review', 'upload', 'upload_date', 'test']);
            $table->boolean('completed')->default(0);

            ########################################
            ###       Approvment Status          ###
            ###                                  ###
            ########################################
            // 1 => Approved
            // -1 => Rejected
            // 0 | Null => 
            $table->enum('approvement_status', [1, -1, 0])->nullable();

            // Content
            $table->string('content_url');
            $table->string('content_file');
            $table->longText('content');

            //
            $table->string('certificate_file')->nullable();
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
        Schema::dropIfExists('requirements');
    }
}
