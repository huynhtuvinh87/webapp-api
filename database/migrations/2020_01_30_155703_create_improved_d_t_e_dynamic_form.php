<?php

use App\Models\DynamicForm;
use App\Models\DynamicFormColumn;
use App\Models\HiringOrganization;
use Illuminate\Database\Migrations\Migration;

// NOTE: Don't need Logs / Exceptions

class CreateImprovedDTEDynamicForm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            // NOTE: DTE Energy id is 78
            $dteHO = HiringOrganization::where('id', 78)
                ->first();

            if(!isset($dteHO)){
                Log::debug("DTE Energy could not be found");
                return;
            }
            $dteOwnerRole = $dteHO->owner;
            $dtOwnerUser = $dteOwnerRole->user();

            // Creating Form
            $originalDF = DynamicForm::create([
                'title' => "DTE Employee Matrix - Improved",
                'create_role_id' => $dteOwnerRole->id,
                'hiring_organization_id' => $dteHO->id,
            ]);

            // Creating columns
            $originalColumns = [
                [
                    "label" => "Employee Information",
                    'type' => 'label',
                ],
                [
                    "label" => "Employee Name",
                    'type' => 'text',
                ],
                [
                    "label" => "Qualification Information",
                    'type' => 'label',
                ],
                [
                    "label" => "DTE Qualification Number",
                    'type' => 'numeric',
                ],
                [
                    "label" => "Qualification Name",
                    'type' => 'text',
                ],
                [
                    "label" => "Evaluator Name",
                    'type' => 'text',
                ],
                [
                    "label" => "Initial Evaluation Information",
                    'type' => 'label',
                ],
                [
                    "label" => "Initial Evaluation Date",
                    'type' => 'text',
                ],
                [
                    "label" => "Evaluation Type",
                    'type' => 'text',
                ],
                [
                    "label" => "Subsequent Evaluation Information",
                    'type' => 'label',
                ],
                [
                    "label" => "Subsequent Evaluation Date",
                    'type' => 'text',
                ],
                [
                    "label" => "Evaluation Type",
                    'type' => 'text',
                ],
                [
                    "label" => "Evaluation Period",
                    'type' => 'text',
                ],
            ];

            foreach ($originalColumns as $index => $column) {
                $column['dynamic_form_id'] = $originalDF->id;
                $column['order'] = $index;
                $column['visible_to_contractors'] = $column['visible_to_contractors'] ?? true;
                DynamicFormColumn::create($column);
            }

        } catch (Exception $e) {
            Log::error("Failed to migrate");
            Log::error($e->getMessage());
            Log::debug($e);
            throw $e;
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

            $df = DynamicForm::where('title', '=', 'DTE Employee Matrix - Improved')->first();

            if (isset($df)) {
                $dfColumns = $df->columns
                    ->each(function ($column) {
                        $column->delete();
                    });

                $df->delete();
            }

        } catch (Exception $e) {
            Log::error("Failed to rollback");
            Log::error($e->getMessage());
            Log::debug($e);
            throw $e;
        }
    }
}
