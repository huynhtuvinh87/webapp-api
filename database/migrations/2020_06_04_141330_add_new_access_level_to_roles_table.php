<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AddNewAccessLevelToRolesTable extends Migration
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

            // These are inaccurate
//            Role::where("access_level", ">", 1)->increment("access_level");
//            Role::where("access_level", ">", 1)->update(["access_level" => DB::raw('access_level + 1')]);
            
            DB::statement("update roles set access_level = access_level + 1 where access_level > 1");

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();

            Log::error(__METHOD__, ['exception' => $e->getMessage()]);
            throw new Exception($e->getMessage());
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

            DB::statement("update roles set access_level = access_level - 1 where access_level > 1");

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();

            Log::error(__METHOD__, ['exception' => $e->getMessage()]);
            throw new Exception($e->getMessage());
        }
    }
}
