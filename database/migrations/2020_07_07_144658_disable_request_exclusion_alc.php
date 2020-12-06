<?php

use App\Models\Requirement;
use App\Models\RequirementContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DisableRequestExclusionAlc extends Migration
{

    private $requirements_to_be_changed = [];

    public function __construct()
    {
        $this->requirements_to_be_changed = [
            "Act 126 Child Abuse Recognition and Reporting Training Record",
            "Act 126 Child Abuse Recognition and Reporting Training Record (Pittsburgh)",
            "Drug Test Results",
            "Passenger Vehicle for Hire (PVH) Driver Permit",
            "Passenger Vehicle for Hire (PVH) Vehicle Permit",
            "Passenger Vehicle for Hire (PVH) Company Operating Permit",
            "CMS Scope of Work",
            "Illinois Driver's License",
            "Wisconsin Driver's License",
            "PennDOT MVR Release Form",
            "SDP MVR Release Form",
            "Motor Vehicle Record",
        ];
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        try {
            DB::beginTransaction();

            foreach ($this->requirements_to_be_changed as $key => $value){

                $requirement_name = addslashes($value);
                $requirement_content = RequirementContent::where('name', "=", DB::raw("'{$requirement_name}'"))->first();

                if(isset($requirement_content) && !is_null($requirement_content)){
                    Requirement::where("id", $requirement_content->requirement_id)->update(['allow_exclusion' => 0]);
                } else {
                    Log::info("Requirement Name " . $requirement_name . " not found.");
                }
            }

            DB::commit();


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
        try {
            DB::beginTransaction();

            foreach ($this->requirements_to_be_changed as $key => $value){

                $requirement_name = addslashes($value);
                $requirement_content = RequirementContent::where('name', "=", DB::raw("'{$requirement_name}'"))->first();

                if(isset($requirement_content) && !is_null($requirement_content)){
                    Log::info("Updating id " . $requirement_content->requirement_id);
                    Requirement::where("id", $requirement_content->requirement_id)->update(['allow_exclusion' => 1]);
                } else {
                    Log::info("Requirement Name " . $requirement_name . " not found.");
                }
            }

            DB::commit();


        } catch (Exception $ex) {
            DB::rollback();
            Log::debug(__METHOD__, ['exception' => $ex->getMessage()]);
        }
    }
}
