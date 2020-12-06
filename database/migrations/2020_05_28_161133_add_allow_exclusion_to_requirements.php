<?php

use App\Models\Requirement;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AddAllowExclusionToRequirements extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 144 = ALC Schools
        $hos_not_allowing_exclusion = [144];

        try {
            DB::beginTransaction();

            if (Schema::hasColumn('requirements', 'allow_exclusion')) {
                Schema::table('requirements', function (Blueprint $table) {
                    $table->dropColumn('allow_exclusion');
                });
            }

            Schema::table('requirements', function (Blueprint $table) {
                $table->boolean('allow_exclusion')->default(1)->after('can_edit');
            });

            DB::commit();

            Requirement::whereIn('hiring_organization_id', $hos_not_allowing_exclusion)
            ->update(['allow_exclusion' => 0]);


        } catch (Exception $ex) {
            DB::rollback();
            Log::debug(__METHOD__, ['exception' => $ex->getMessage()]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('requirements', 'allow_exclusion')) {
            Schema::table('requirements', function (Blueprint $table) {
                $table->dropColumn('allow_exclusion');
            });
        }
    }
}
