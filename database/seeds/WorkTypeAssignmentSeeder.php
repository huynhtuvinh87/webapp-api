<?php

use Illuminate\Database\Seeder;
use App\Models\Contractor;
use App\Models\WorkType;

class WorkTypeAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();
        $contractorCount = 10;
        $workTypeCount = 10;
        $rootNCISTestCode = '99';

        $workTypes = [];

        $testWorkTypes = array(
            0 =>
            array(
                "title" => "1 Contractor",
                "code" => 01
            ),
            array(
                "title" => "1 Contractor, 2 Work types (first)",
                "code" => 02
            ),
            array(
                "title" => "1 Contractor, 2 Work types (second)",
                "code" => 03
            ),
            array(
                "title" => "2 Contractors, 1 work type",
                "code" => 04
            ),
            array(
                "title" => "2 Contractors, 2 work types (first)",
                "code" => 05
            ),
            array(
                "title" => "2 Contractors, 2 work types (second)",
                "code" => 06
            )
        );

        try {
            DB::beginTransaction();

            // Get / make Contractors
            $contractors = factory(Contractor::class, $contractorCount)->create();

            // Error checking contractors
            if (sizeof($contractors) != $contractorCount) {
                throw new Exception("Contractor list was not generated properly");
            }

            // Make test work types

            // Getting / creating level 1 (root) Test NCIS code
            $rootTestWorkType = WorkType::where('code', $rootNCISTestCode)->get()->first();
            if (!isset($rootTestWorkType)) {
                $rootTestWorkType = factory(WorkType::class)->create([
                    'parent_id' => null,
                    'code' => $rootNCISTestCode,
                    'name' => 'Testing Work Type',
                ]);
            }

            // Creating level 2 work types
            for ($i = 0; $i < $workTypeCount; $i++) {
                // NOTE: Putting creator in a for loop so each instance is unique with a new $faker value

                // Getting work type title
                // Default to a random faker value, if its not in the pre-defined titles
                $workTypeTitle = $faker->unique()->jobTitle;
                if (isset($testWorkTypes[$i])) {
                    $workTypeTitle = $testWorkTypes[$i]['title'];
                }

                $workTypeCode = $rootNCISTestCode . $faker->unique()->numberBetween(11, 99);
                if (isset($testWorkTypes[$i])){
                    $workTypeCode = $rootNCISTestCode . $testWorkTypes[$i]['code'];
                }

                $newWorkType = factory(WorkType::class)->create([
                    'parent_id' => $rootTestWorkType->id,
                    'code' => $workTypeCode,
                    'name' => $workTypeTitle
                ]);

                array_push($workTypes, $newWorkType);
            }

            // Checking Database for Work Types
            $dbWorkType = WorkType::where('parent_id', $rootTestWorkType->id)->get();
            // Making sure that the work type elements are all present

            // Creating relations between contractors and work types
            DB::table('contractor_work_type')->insert([
                // PAIR 0: 1 Contractor (0) : 1 Position (0)
                0 => array(
                    "work_type_id" => $workTypes[0]->id,
                    "contractor_id" => $contractors[0]->id,
                ),

                // PAIR 1 - 2: 1 Contractor(1) : * Positions (1 - 2)
                1=> array(
                    "work_type_id" => $workTypes[1]->id,
                    "contractor_id" => $contractors[1]->id,
                ),
                2 => array(
                    "work_type_id" => $workTypes[2]->id,
                    "contractor_id" => $contractors[1]->id,
                ),

                // PAIR 3 - 4: * Contractor(2 - 3) : 1 Positions (3)
                3=> array(
                    "work_type_id" => $workTypes[3]->id,
                    "contractor_id" => $contractors[2]->id,
                ),
                4 => array(
                    "work_type_id" => $workTypes[3]->id,
                    "contractor_id" => $contractors[3]->id,
                ),

                // PAIR 5: * Contractor(4-5) : * Positions (4-5)
                5=> array(
                    "work_type_id" => $workTypes[4]->id,
                    "contractor_id" => $contractors[4]->id,
                ),
                6 => array(
                    "work_type_id" => $workTypes[4]->id,
                    "contractor_id" => $contractors[5]->id,
                ),

                7=> array(
                    "work_type_id" => $workTypes[5]->id,
                    "contractor_id" => $contractors[4]->id,
                ),
                8 => array(
                    "work_type_id" => $workTypes[5]->id,
                    "contractor_id" => $contractors[5]->id,
                )
            ]);


            // TODO: Insert some pre-made relations
            // * 1 Contractor : 1 Position
            // * Multiple Contractors : 1 Position
            // * 1 Contractor : Multiple positions
            // * Multiple contractors : Multiple positions

            // Saving data to DB
            $rootTestWorkType->save();
            // $workTypes->each(function ($workType){
            //     $workType->save();
            // });

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
