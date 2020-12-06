<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixOnDeleteForForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $sets = [
            [
                'name' => 'dynamic_form_submissions',
                'keys' => [
                    // When a requirement history entry is deleted
                    // delete the submission
                    [
                        'keyName' => 'dynamic_form_submissions_requirement_history_id_foreign',
                        'sourceColumn' => 'requirement_history_id',
                        'targetTable' => 'requirement_histories',
                        'targetColumn' => 'id',
                        'onDelete' => 'set null'
                    ],
                ],
            ],
            [
                'name' => 'dynamic_form_submission_data',
                'keys' => [
                    // When a submission is deleted
                    // delete the submission data
                    [
                        'keyName' => 'dynamic_form_submission_data_dynamic_form_submission_id_foreign',
                        'sourceColumn' => 'dynamic_form_submission_id',
                        'targetTable' => 'dynamic_form_submissions',
                        'targetColumn' => 'id',
                        'onDelete' => 'cascade'
                    ],
                ],
            ],
            [
                'name' => 'ratings',
                'keys' => [
                    // When a submission is deleted
                    // delete the rating
                    [
                        'keyName' => 'ratings_dynamic_form_submission_id_foreign',
                        'sourceColumn' => 'dynamic_form_submission_id',
                        'targetTable' => 'dynamic_form_submissions',
                        'targetColumn' => 'id',
                        'onDelete' => 'cascade'
                    ],
                ],
            ],
        ];

        try {
            DB::beginTransaction();

            foreach ($sets as $table) {
                foreach ($table['keys'] as $keySet) {
                    $this->fixForeignKey(
                        $table['name'],
                        $keySet['keyName'],
                        $keySet['sourceColumn'],
                        $keySet['targetTable'],
                        $keySet['targetColumn'],
                    );
                }
            }

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
        //
    }

    public function fixForeignKey($sourceTableName, $keyName, $sourceColumn, $targetTable, $targetColumn)
    {
        // DB::statement("ALTER TABLE `$sourceTableName` DROP FOREIGN KEY `$keyName`");

        $this->dropForeignIfExists($sourceTableName, $keyName);
        DB::commit();

        Schema::table($sourceTableName, function (Blueprint $table) use ($keyName, $sourceColumn, $targetTable, $targetColumn) {
            $table->foreign($sourceColumn)
                ->references($targetColumn)
                ->on($targetTable)
                ->onDelete('set null');
        });
    }

    public function dropForeignIfExists($sourceTableName, $keyName)
    {
        try {
            Schema::table($sourceTableName, function (Blueprint $table) use ($keyName) {
                $table->dropForeign($keyName);
            });
        } catch (Exception $e) {
            // Do nothing
        }
    }
}
