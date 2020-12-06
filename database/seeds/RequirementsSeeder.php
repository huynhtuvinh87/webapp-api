<?php

use Illuminate\Database\Seeder;

use App\Models\Requirement;
use App\Models\DynamicForm;
use App\Models\DynamicFormColumn;
use App\Models\RequirementContent;
use App\Models\HiringOrganization;
use App\Models\Test;

class RequirementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = new Faker\Generator();
        $requirements = [];

        // Creating Fake Organization
        $fakeHiringOrg = factory(HiringOrganization::class)->create();
        $hiringOrgId = $fakeHiringOrg->id;

        // Creating requirement
        $randomRequirement = factory(Requirement::class)->create([
            'hiring_organization_id' => $hiringOrgId,
        ]);

        $testRequirement = factory(Requirement::class)->create([
            'hiring_organization_id' => $hiringOrgId,
            'type' => 'test'
        ]);

        array_push($requirements, $randomRequirement);
        array_push($requirements, $testRequirement);

        foreach ($requirements as $requirement) {
            // Once requirement is created, then create associated requirement type
            if ($requirement->type == 'test') {
                $fakeTest = factory(Test::class)->create([
                    'hiring_organization_id' => $hiringOrgId,
                ]);

                // Connecting test to requirement
                $requirement->integration_resource_id = $fakeTest->id;
                $requirement->save();

            } else if ($requirement->type == 'form'){

                // Seeding random form
                $form = factory(DynamicForm::class)->create([
                    'create_role_id' => 1
                ]);
                $columns = factory(DynamicFormColumn::class, 4)->create([
                    'dynamic_form_id' => $form->id
                ]);

                // Setting up requirement link
                $requirement->integration_resource_id = $form->id;
                $requirement->save();

            } else {
                // Creating requirement content if its not a test
                $requirementContent = factory(RequirementContent::class)->create([
                    'requirement_id' => $requirement->id
                ]);
            }
        }

        // TODO: Create a department factory, call it here, and add the requirement to the department
    }
}
