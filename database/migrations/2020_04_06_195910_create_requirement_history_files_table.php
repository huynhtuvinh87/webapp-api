<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRequirementHistoryFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            DB::beginTransaction();

            Schema::dropIfExists('file_requirement_histories');
            Schema::dropIfExists('file_requirement_history');

            Schema::create('file_requirement_history', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('file_id');
                $table->bigInteger('requirement_history_id');
                $table->timestamps();

                //Index used here because we will be using it often to get related files
                $table->index('requirement_history_id');
            });

            $fileInsertQueryData = DB::table('requirement_histories')
                ->select([
                    "file_id",
                    "id as requirement_history_id",
                ])
                ->whereNotNull('file_id')
                ->get()
                ->map(function ($fileData) {
                    return [
                        'requirement_history_id' => $fileData->requirement_history_id,
                        'file_id' => $fileData->file_id,
                    ];
                })
                ->chunk(500)
                ->each(function ($chunk) {
                    $data = $chunk->toArray();
                    DB::table('file_requirement_history')
                        ->insert($data);
                });

            Schema::table('requirement_histories', function (Blueprint $table) {
                $table->dropColumn('file_id');
            });

            DB::commit();

        } catch (Exception $ex) {
            Log::error($ex);
            DB::rollback();
            throw $ex;
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requirement_histories', function (Blueprint $table) {
            $table->bigInteger('file_id')->nullable()->default(null);
        });
        Schema::dropIfExists('file_requirement_history');
    }
}
