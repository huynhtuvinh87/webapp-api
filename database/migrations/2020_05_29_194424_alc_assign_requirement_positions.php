<?php

use App\Models\HiringOrganization;
use App\Models\Position;
use App\Models\Requirement;
use App\Models\RequirementContent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Facades\Excel;

class AlcAssignRequirementPositions extends Migration
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

            Excel::import(new ValidateInformation($this), storage_path('alc_requirements_positions_2wave.xlsx'));

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            Log::error(__METHOD__, ['exception' => $e->getMessage()]);
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

class ValidateInformation implements ToCollection
{

    public function collection(Collection $rows)
    {
        Log::info("Starting validating file");
        $valid_content = true;
        $alc_id = 144;
        $hiringOrg = HiringOrganization::find($alc_id);

        if(!isset($hiringOrg)){
			Log::warn("Hiring Org could not be found");
			if(config('app.env') != 'development'){
				throw new Exception("Hiring Org could not be found");
			}
            return;
        }

        foreach ($rows as $index => $row) {

            if ($index > 0) {

                //Validation
                $requirement_content = RequirementContent::where('name', trim($row[0]))->count();

                if (!$requirement_content) {
                    Log::info("Requirement `$row[0]` not found. Line $index");
                    $valid_content = false;
                }

                $position_found = Position::where('name', trim($row[1]))
                    ->where('position_type', trim($row[2]))
                    ->where('hiring_organization_id', $alc_id)
                    ->count();

                if (!$position_found) {

                    Log::info("Position `$row[1]` type `$row[2]` not found. Line $index");
                    $valid_content = false;

                } else {

                    $position = Position::where('name', trim($row[1]))
                        ->where('position_type', trim($row[2]))
                        ->where('hiring_organization_id', $alc_id)
                        ->first();

                    $requirement = RequirementContent::where('name', trim($row[0]))->first();

                    $is_requirement_attached = DB::table('position_requirement')
                        ->where('position_id', $position->id)
                        ->where('requirement_id', $requirement->requirement_id)
                        ->count();

                    if (!$is_requirement_attached) {
                        $position->requirements()->attach($requirement->requirement_id);
                    } else {
                        Log::info("Position $position->id already attached with requirement $requirement->requirement_id");
                    }

                }

            }
        }

        Log::info("Finished validating file");

    }
}
