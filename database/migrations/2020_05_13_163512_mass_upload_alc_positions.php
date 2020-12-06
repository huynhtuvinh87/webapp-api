<?php

use App\Models\Facility;
use App\Models\Position;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Facades\Excel;

class MassUploadAlcPositions extends Migration
{
    public function up()
    {
        try {
            DB::beginTransaction();
            // Cleaning newly created positions and test positions.
            // The POC positions should stay
            $positions = Position::where('hiring_organization_id', 144)
                ->where('id', '>=', 1699)
                ->get()
                ->map(function($position){ return $position->id; })
                ->toArray();

            DB::table('facility_position')->whereIn('position_id', $positions)->delete();
            DB::table('position_requirement')->whereIn('position_id', $positions)->delete();
            DB::table('position_role')->whereIn('position_id', $positions)->delete();
            DB::table('positions')->whereIn('id', $positions)->delete();

            Log::info("Test ALC Positions deleted");

            Excel::import(new PositionsImport($this), storage_path('alc_positions.xlsx'));
            DB::commit();
        } catch
        (Exception $e) {
            DB::rollback();
            Log::debug(__METHOD__, ['exception' => $e->getMessage()]);
        }
    }

    public function down()
    {
        try {
            DB::beginTransaction();
            // Cleaning newly created positions and test positions.
            // The POC positions should stay

            $positions = Position::where('hiring_organization_id', 144)
                ->where('id', '>=', 1699)
                ->get()
                ->map(function($position){ return $position->id; })
                ->toArray();

            DB::table('facility_position')->whereIn('position_id', $positions)->delete();
            DB::table('position_requirement')->whereIn('position_id', $positions)->delete();
            DB::table('position_role')->whereIn('position_id', $positions)->delete();
            DB::table('positions')->whereIn('id', $positions)->delete();

            DB::commit();
        } catch
        (Exception $e) {
            DB::rollback();
            Log::debug(__METHOD__, ['exception' => $e->getMessage()]);
        }
    }
}

class PositionsImport implements ToCollection
{

    public function collection(Collection $rows)
    {
        Log::info("Starting mass importing ALC positions");
        foreach ($rows as $index => $row) {
            if ($index > 0) {

                $position = Position::firstOrCreate(
                    ['name' => $row[1]],
                    ['hiring_organization_id' => 144, 'position_type' => $row[0], 'auto_assign' => $row[2]]
                );
//                Log::info(json_encode($position));

//                Log::info("Looking for facility " . trim($row[3]));
                $facility = Facility::where('name', trim($row[3]))->first();

                if (!isset($facility) || !$facility) {
                    Log::info("Facility $row[3] not found, it will be created, line $index in the Excel file");
                    $facility = Facility::create(['name' => trim($row[3]), 'hiring_organization_id' => 144]);
                }

                $is_attached = DB::table('facility_position')
                    ->where('position_id', $position->id)
                    ->where('facility_id', $facility->id)
                    ->count();

                if (!$is_attached) {
                    $position->facilities()->attach($facility->id);
                }

//                Log::info("Looking for requirement " . trim($row[4]));
                $requirement = DB::table('requirement_contents')
                    ->join('requirements', 'requirements.id', '=', 'requirement_contents.requirement_id')
                    ->where('requirements.hiring_organization_id', 144)
                    ->where('requirement_contents.name', trim($row[4]))
                    ->first();

                if (!isset($requirement)) {
                    Log::info("Requirement $row[4] not found, it wont attach.");
                } else {

                    $is_requirement_attached = DB::table('position_requirement')
                        ->where('position_id', $position->id)
                        ->where('requirement_id', $requirement->id)
                        ->count();

                    if (!$is_requirement_attached) {
                        $position->requirements()->attach($requirement->id);
                    }

                }

                Log::info($index . ": " . $row[1]);
            }
        }
        Log::info("Finished importing ALC positions");
    }
}
