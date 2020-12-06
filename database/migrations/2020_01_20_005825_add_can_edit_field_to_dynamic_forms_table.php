<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCanEditFieldToDynamicFormsTable extends Migration
{
    private $columnName = 'can_edit';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        try {
            DB::beginTransaction();


            Schema::table('dynamic_forms', function (Blueprint $table) {
                // Dropping column if it already exists
                if (Schema::hasColumn('dynamic_forms', $this->columnName)) {
                    Schema::table('dynamic_forms', function (Blueprint $table) {
                        $table->dropColumn($this->columnName);
                    });
                }

                $table->boolean($this->columnName)
                    ->default(true)
                    ->comment('Enable the ability for users to edit this table');
            });

            DB::commit();
        } catch (Exception $e) {
            Log::error($e);
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dynamic_forms', function (Blueprint $table) {
            $table->dropColumn($this->columnName);
        });
    }
}
