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

class AlcAssignMoreRequirementPositions extends Migration
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

            Excel::import(new ImportData($this), storage_path('alc_requirements_positions_3wave.xlsx'));

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            Log::error(__METHOD__, ['exception' => $e->getMessage()]);
            throw new Exception("Failed to migrate");
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

class ImportData implements ToCollection
{

    public function collection(Collection $rows)
    {
        Log::info("Starting validating file");
        $alc_id = 144;

        $hiringOrg = HiringOrganization::find($alc_id);

        if(!isset($hiringOrg)){
			Log::warn("Hiring Org not defined");
			if(config('app.env') != 'development'){
				throw new Exception("Hiring Org was not defined");
			}
            return;
        }

        foreach ($rows as $index => $row) {

            if ($index > 0) {

                $requirement_content = RequirementContent::where('name', trim($row[0]))->first();

                if (!$requirement_content || is_null($requirement_content)) {
                    Log::info("Requirement `$row[0]` not found. Line $index");
                    throw new Exception("Requirement `$row[0]` not found. Line $index");
                }

                $position = Position::where('name', trim($row[1]))
                    ->where('position_type', trim($row[2]))
                    ->where('hiring_organization_id', $alc_id)
                    ->first();

                if (!$position || is_null($position)) {
                    Log::info("Position `$row[1]` type `$row[2]` not found. Line $index");
                    throw new Exception("Position `$row[1]` type `$row[2]` not found. Line $index");
                } else {

                    $is_requirement_attached = DB::table('position_requirement')
                        ->where('position_id', $position->id)
                        ->where('requirement_id', $requirement_content->requirement_id)
                        ->count();

                    if (!$is_requirement_attached) {
                        Log::info("Attaching $position->name to requirement $requirement_content->name");
                        $position->requirements()->attach($requirement_content->requirement_id);
                    } else {
                        Log::info("Position $position->name already attached with requirement $requirement_content->name");
                    }

                }

            }
        }

        Log::info("Finished validating file");

    }
}
