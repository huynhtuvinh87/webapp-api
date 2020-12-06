<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDefaultAccessRoleTable extends Migration
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
            DB::statement("ALTER TABLE roles MODIFY COLUMN access_level int DEFAULT 4 NOT NULL COMMENT '1=read only, 2=uploader, 3=approver, 4=full access'");
            DB::update('UPDATE roles set access_level = 4 WHERE `role` = "owner"'); //contractor and hiring_orgs
            DB::commit();
        } catch (Exception $e){
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
            DB::statement("ALTER TABLE roles MODIFY COLUMN access_level int DEFAULT 3 NOT NULL COMMENT '1=read only, 2=approver, 3=full access'");
            DB::commit();
        } catch (Exception $e){
            DB::rollback();
            Log::debug(__METHOD__, ['exception' => $e->getMessage()]);
        }
    }
}
