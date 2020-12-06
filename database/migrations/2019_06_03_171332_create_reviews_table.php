<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::drop('ratings');

        Schema::create('ratings', function (Blueprint $table) {
            $table->increments('id');
            $table->text('comments')->nullable();
            $table->integer('rating');
            $table->timestamps();

            $table->unsignedInteger('contractor_id');
            $table->unsignedInteger('hiring_organization_id');

            $table->unique(['contractor_id', 'hiring_organization_id']);
            $table->foreign('contractor_id')->references('id')->on('contractors')->onDelete('cascade');
            $table->foreign('hiring_organization_id')->references('id')->on('hiring_organizations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reviews');
    }
}
