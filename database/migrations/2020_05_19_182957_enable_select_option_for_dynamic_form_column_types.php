<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class EnableSelectOptionForDynamicFormColumnTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE dynamic_form_columns
		MODIFY COLUMN type enum('label','text','numeric','checkbox','transformation','image','textarea','radio','select')
		NOT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE dynamic_form_columns
		MODIFY COLUMN type enum('label','text','numeric','checkbox','transformation','image','textarea','radio')
		NOT NULL;");
    }
}
