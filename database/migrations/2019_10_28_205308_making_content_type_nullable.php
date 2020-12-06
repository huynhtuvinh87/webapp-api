<?php

use Illuminate\Database\Migrations\Migration;

class MakingContentTypeNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		// Adding `none` option
        DB::statement("ALTER TABLE requirements
		MODIFY COLUMN content_type enum('text','file','url', 'none')
		NOT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		// Returning column to original state
		DB::statement("ALTER TABLE requirements
		MODIFY COLUMN content_type enum('text','file','url')
		NOT NULL;");
    }
}
