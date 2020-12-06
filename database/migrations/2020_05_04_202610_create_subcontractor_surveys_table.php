<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CreateSubcontractorSurveysTable extends Migration
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
            DB::beginTransaction();

            Schema::dropIfExists('subcontractor_surveys');

            Schema::create('subcontractor_surveys', function (Blueprint $table) {
                $table->increments('id');
                $table->enum('entity_key', ['contractor', 'hiring_organization']);
                $table->integer('entity_id');
                $table->unsignedInteger('role_id');
                $table->enum('answer', ['yes', 'no', 'later']);
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('role_id')->references('id')->on('roles');
            });

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            Log::error("Failed to migrate " . __FILE__);

            if (config('app.env') != 'development') {
                throw new Exception($e->getMessage());
            }
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws Exception
     */
    public function down()
    {
        try {
            DB::beginTransaction();
            Schema::dropIfExists('subcontractor_surveys');
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            Log::error("Failed to migrate " . __FILE__);

            if (config('app.env') != 'development') {
                throw new Exception($e->getMessage());
            }
        }
    }
}
