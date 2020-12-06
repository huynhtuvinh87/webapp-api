<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RatingTableAdjustments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('ratings', function(Blueprint $table){
			// Change the ratings table, rating column to be a decimal value with a max of 100
			$table->decimal('rating',4,1)->change();

			// Adding new column to rating table to store the rating type
			$table->string('rating_system')
				->default('star')
				->comment("Rating system this rating is involved with. Example: star / form");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::table('ratings', function(Blueprint $table){
			$table->decimal('rating',2,1)->change();
			$table->dropColumn('rating_system');
        });
    }
}
