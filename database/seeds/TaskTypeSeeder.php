<?php

use App\Models\TaskType;
use Illuminate\Database\Seeder;

class TaskTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Creating faker
        $faker = Faker\Factory::create();

        // Creating array of task types to be put into db
        $taskTypes = [
            [
                "name" => "Observation",
                "description" => "",
                "is_active" => 1,
            ],
            [
                "name" => "Feedback",
                "description" => "",
                "is_active" => 1,
            ],
            [
                "name" => "Opportunity for Improvement",
                "description" => "",
                "is_active" => 1,
            ],
            [
                "name" => "Inspection",
                "description" => "",
                "is_active" => 1,
            ],

        ];

        try {

            DB::beginTransaction();

            foreach ($taskTypes as $task) {

                // Check to see if task type already exists
                // Checking by name
                $taskType = TaskType::where('name', $task['name'])->get();
                $taskTypeExists = sizeof($taskType) > 0;

                if ($taskTypeExists) {
                    // If the task exists, then update it with the new val
                    TaskType::where('name', $task['name'])->update([
                        "description" => $task['description'],
                        "is_active" => $task['is_active'],
                    ]);
                } else {
                    // Create if the task doesn't exist
                    TaskType::create([
                        "name" => $task['name'],
                        "description" => $task['description'],
                        "is_active" => $task['is_active'],
                    ]);
                }
            }

            DB::commit();

        } catch (Exception $e) {
            DB::rollback();
            Log::error("Failed to execute seed TaskTypeSeeder.\n\nError: " . $e);
            throw $e;
        }
    }
}
