<?php

use App\Models\Facility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateFacilitiesForAlc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 144 = ALC Schools
        $ho_id = [144];

        try {
            DB::beginTransaction();

            Facility::whereIn('hiring_organization_id', $ho_id)->update(['display_on_registration' => 0]);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();

            Log::error(__METHOD__, ['exception' => $exception->getMessage()]);
            throw new Error("Failed to migrate");
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
}
