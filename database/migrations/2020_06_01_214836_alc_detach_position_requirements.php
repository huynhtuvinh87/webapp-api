<?php

use App\Models\HiringOrganization;
use App\Models\Position;
use App\Models\RequirementContent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Facades\Excel;

class AlcDetachPositionRequirements extends Migration
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

            Excel::import(new ValidateDelete($this), storage_path('alc_delete_relation.csv'));

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

class ValidateDelete implements ToCollection
{

    public function collection(Collection $rows)
    {
        Log::info("Starting validating deleting file");
        $alc_id = 144;

        $hiringOrg = HiringOrganization::find($alc_id);

        if(!isset($hiringOrg)){
			Log::warn("Hiring Org not defined");
			if(config('app.env') != 'development'){
				throw new Exception("Hiring org not defined");
			}
            return;
        }

        foreach ($rows as $index => $row) {

            if ($index > 0) {

                //Validation
                $requirement_content = RequirementContent::where('name', trim($row[0]))->count();

                if (!$requirement_content) {
                    Log::info("Requirement `$row[0]` not found. Line $index");
                }

                $position_found = Position::where('name', trim($row[1]))
                    ->where('position_type', trim($row[2]))
                    ->where('hiring_organization_id', $alc_id)
                    ->count();

                if (!$position_found) {

                    Log::info("Position `$row[1]` type `$row[2]` not found. Line $index");

                } else {

                    $position = Position::where('name', trim($row[1]))
                        ->where('position_type', trim($row[2]))
                        ->where('hiring_organization_id', $alc_id)
                        ->first();

                    $requirement_content = RequirementContent::where('name', trim($row[0]))->first();

                    DB::table('position_requirement')
                        ->where('position_id',  $position->id)
                        ->where('requirement_id',  $requirement_content->requirement_id)
                        ->delete();

                }

            }
        }

        Log::info("Finished validating deleting file");

    }
}
