<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVisibleToContractorsColumnToDynamicFormColumnsTable extends Migration
{
    private $tableName = 'dynamic_form_columns';
    private $columnName = 'visible_to_contractors';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            DB::beginTransaction();

            Schema::table($this->tableName, function (Blueprint $table) {
                // Dropping column if it already exists
                if (Schema::hasColumn($this->tableName, $this->columnName)) {
                    Schema::table($this->tableName, function (Blueprint $table) {
                        $table->dropColumn($this->columnName);
                    });
                }

                $table->boolean($this->columnName)
                    ->default(true)
                    ->comment('Column is visible to contractors');
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
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropColumn($this->columnName);
        });
    }
}
