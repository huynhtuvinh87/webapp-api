<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDatatypeDynamicFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // NOTE: Using statement as laravel doesn't play nicely with enums
        // dynamic_form_columns has enums
        DB::statement('ALTER TABLE dynamic_form_columns MODIFY label TEXT NOT NULL');
        DB::statement('ALTER TABLE dynamic_form_columns MODIFY description TEXT NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE dynamic_form_columns MODIFY label VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE dynamic_form_columns MODIFY description VARCHAR(255) NULL');
    }
}
