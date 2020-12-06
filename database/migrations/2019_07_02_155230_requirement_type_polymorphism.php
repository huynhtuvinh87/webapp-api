<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RequirementTypePolymorphism extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Tried using `renameColumn`, but it was throwing an error which seemed to require a lot of overhead work to get it to work


        // Creating integration_resource_id column
        // Creating ID to point to id of the requirement
        // Polymorphic - table that it points to is based on the `type` column
        Schema::table('requirements', function (Blueprint $table) {
            $table->unsignedBigInteger('integration_resource_id')->nullable();
        });

        // Preserving the test_id value by moving the test_id value into integration_resource_id
        $testIds = DB::table('requirements')->select('id', 'test_id')->get();
        foreach ($testIds as $testId) {
            if(!isset($testId->id)){
                throw new Exception("Test ID was not set: " . $testId->id);
            }
            DB::table('requirements')
                ->where('id', $testId->id)
                ->update([
                    'integration_resource_id' => $testId->id,
                ]);
        }

        Schema::table('requirements', function (Blueprint $table) {
            $hasColTestId = Schema::hasColumn('requirements', 'test_id');

            // Removing old test_id column
            if ($hasColTestId) {
                $table->dropColumn('test_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('requirements', function (Blueprint $table) {
            $hasColTestId = Schema::hasColumn('requirements', 'test_id');
            $hasColRequirementTypeId = Schema::hasColumn('requirements', 'integration_resource_id');

            if (!$hasColTestId) {
                $table->unsignedBigInteger('test_id')->nullable();
            }
            if ($hasColRequirementTypeId) {
                $table->dropColumn('integration_resource_id');
            }
        });
    }
}
