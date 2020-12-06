<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContractorsFkFolders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws Exception
     */
    public function up()
    {
        try {

            Schema::table('file_folder', function (Blueprint $table) {
                $table->unsignedInteger('contractor_id')->comment('FK contractor')->after('folder_id');
                $table->foreign('contractor_id')->references('id')->on('contractors');
            });

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
        Schema::table('file_folder', function (Blueprint $table) {
            $table->dropColumn('contractor_id');
        });
    }
}
