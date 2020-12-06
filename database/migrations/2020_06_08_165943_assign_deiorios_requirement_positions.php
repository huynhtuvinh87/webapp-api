<?php

use App\Models\Position;
use App\Models\Requirement;
use App\Models\RequirementContent;
use App\Models\Facility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Facades\Excel;

class AssignDeioriosRequirementPositions extends Migration
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

            Excel::import(new ImportDeioriosData($this), storage_path('deiorios.xlsx'));

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

class ImportDeioriosData implements ToCollection
{

    public function collection(Collection $rows)
    {
        Log::info("Starting validating file");
        $deiorios_id = 145;

        foreach ($rows as $index => $row) {

            if ($index > 0) {

                $requirement_content = RequirementContent::where('name', trim($row[0]))->first();

                if (!$requirement_content || is_null($requirement_content)) {
                    Log::error("Requirement `$row[0]` not found. Line $index");
                    continue;
                    //throw new Exception("Requirement `$row[0]` not found. Line $index");
                }

                $position = Position::where('name', trim($row[1]))
                    ->where('hiring_organization_id', $deiorios_id)
                    ->first();

                if (!$position || is_null($position)) {
                    Log::error("Position `$row[1]` not found. Line $index");
                    continue;
                    //throw new Exception("Position `$row[1]` not found. Line $index");
                }
                

                $is_requirement_attached = DB::table('position_requirement')
                    ->where('position_id', $position->id)
                    ->where('requirement_id', $requirement_content->requirement_id)
                    ->count();



                if (!$is_requirement_attached) {
                    Log::info("Attaching position $position->name to requirement $requirement_content->name");
                    $position->requirements()->attach($requirement_content->requirement_id);
                } else {
                    Log::info("Position $position->name already attached with requirement $requirement_content->name");
                }
            }
        }

        Log::info("Finished validating file");

    }
}