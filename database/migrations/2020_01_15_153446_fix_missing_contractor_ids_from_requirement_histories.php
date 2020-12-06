<?php

use App\Models\Contractor;
use App\Models\RequirementHistory;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

class FixMissingContractorIdsFromRequirementHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Grabbing all requirement histories where contractor ID is missing

        $badRequirementHistories = RequirementHistory::whereNull('contractor_id')
            ->get();

        foreach ($badRequirementHistories as $requirementHistory) {

            try {

                // Getting role
                $rhRole = $requirementHistory->role;
                if (!isset($rhRole)) {
                    throw new Exception("Could not determine role, could be deleted");
                }

                // Getting company
                $company = $rhRole->company;
                if (!isset($company)) {
                    throw new Exception("Could not determine company through role");
                }

                // Making sure company is a contractor
                if (get_class($company) != Contractor::class) {
                    throw new Exception("Requirement History was not completed by a Contractor");
                }

                $contractorId = $company->id;

                if (!isset($contractorId)) {
                    throw new Exception("Could not determine contractor ID for requirement history $contractorId");
                }

                $requirementHistory->update([
                    'contractor_id' => $contractorId,
                ]);

            } catch (Exception $e) {
                Log::warn($e->getMessage(), [
                    'requirement_history_id' => $requirementHistory->id,
                ]);
            }
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
