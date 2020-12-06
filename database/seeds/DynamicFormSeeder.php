<?php

use App\Models\DynamicForm;
use App\Models\DynamicFormColumn;
use App\Models\DynamicFormSubmission;
use App\Models\DynamicFormSubmissionAction;
use App\Models\User;
use Illuminate\Database\Seeder;

class DynamicFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * php artisan db:seed --class=DynamicFormSeeder
     *
     * @return void
     */
    public function run()
    {
        $user = factory(User::class)->create();

        // Creating dynamic form
        $form = factory(DynamicForm::class)->create([
            'create_role_id' => $user->highestRole->id,
        ]);

        // Creating standard columns
        $controlTypes = with(new DynamicFormColumn)->getControlTypes();
        foreach ($controlTypes as $controlType) {
            $standardColumns = factory(DynamicFormColumn::class)->create([
                'dynamic_form_id' => $form->id,
                'type' => $controlType,
            ]);
        }

        // Creating random Columns
        $columns = factory(DynamicFormColumn::class, 4)->create([
            'dynamic_form_id' => $form->id,
        ]);

        // Setting dummy transformation
        foreach ($columns as $column) {
            if ($column->type == 'transformation') {
                // Getting random other column to reference
                $otherColumn_i = rand(0, sizeof($columns) - 1);
                $otherColumn = $columns[$otherColumn_i];

                // Setting column to check to see if the other column is apples
                $column->transformation = "{\"==\": [\"apples\", \"{{" . $otherColumn->label . "}}\"]}";
            }
        }

        // Post submission actions
        $postActions = factory(DynamicFormSubmissionAction::class)->create([
            'dynamic_form_id' => $form->id,
            'dynamic_form_column_label' => $columns[0]->label,
        ]);

        // Creating submission on columns
        $submissions = factory(DynamicFormSubmission::class)->create([
            'dynamic_form_id' => $form->id,
            'dynamic_form_model' => json_encode($form->getResponseModel()),
        ]);

    }
}
