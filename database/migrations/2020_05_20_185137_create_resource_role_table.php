<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CreateResourceRoleTable extends Migration
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
            Schema::dropIfExists('role_resource');
            Schema::create('resource_role', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('resource_id');
                $table->unsignedInteger('role_id');
                $table->timestamps();

                $table->foreign('resource_id')->references('id')->on('resources')->onDelete('cascade');
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            });
            DB::commit();
        } catch
        (Exception $e) {
            DB::rollback();
            Log::debug(__METHOD__, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        try {
            DB::beginTransaction();
            Schema::table('resource_role', function (Blueprint $table) {
                $table->dropForeign('resource_role_resource_id_foreign');
                $table->dropForeign('resource_role_role_id_foreign');
            });
            Schema::dropIfExists('resource_role');
            DB::commit();
        } catch
        (Exception $e) {
            DB::rollback();
            Log::debug(__METHOD__, ['exception' => $e->getMessage()]);
        }
    }
}
