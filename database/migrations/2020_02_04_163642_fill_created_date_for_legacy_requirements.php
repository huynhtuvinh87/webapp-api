<?php

use App\Models\RequirementHistory;
use Illuminate\Database\Migrations\Migration;

class FillCreatedDateForLegacyRequirements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $empty_created_at = RequirementHistory::whereNull('created_at')->get();

        foreach ($empty_created_at as $requirement) {
            $requirement->created_at = '1999-09-09 09:09:09';
            $requirement->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $revert_created_at = RequirementHistory::where('created_at', '1999-09-09 09:09:09')->get();

        foreach ($revert_created_at as $requirement) {
            $requirement->created_at = NULL;
            $requirement->save();
        }
    }
}
