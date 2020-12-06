<?php

use App\Models\File;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileSize extends Migration
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

            Schema::table('files', function (Blueprint $table) {
                // Dropping column if it already exists
                if (Schema::hasColumn('files', 'storage_size')) {
                    Schema::table('files', function (Blueprint $table) {
                        $table->dropColumn('storage_size');
                    });
                }

                $table->text('storage_size')
                    ->nullable()
                    ->comment('Size of file in bytes');
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
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('storage_size');
        });
    }
}
