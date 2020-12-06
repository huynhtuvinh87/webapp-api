<?php

use App\Models\DynamicForm;
use App\Models\DynamicFormColumn;
use App\Models\HiringOrganization;
use Illuminate\Database\Migrations\Migration;

// NOTE: Don't need Logs / Exceptions

class CreateDTEDynamicForm extends Migration
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
                'title' => "DTE Employee Matrix - Original",
                'create_role_id' => $dteOwnerRole->id,
                'hiring_organization_id' => $dteHO->id,
            ]);

            // Creating columns
            $originalColumns = [
                [
                    "label" => "Company Name",
                    'type' => 'text',
                ],
                [
                    "label" => "Employee Name",
                    'type' => 'text',
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
                    "label" => "Date of Initial",
                    'type' => 'text',
                ],
                [
                    "label" => "Evaluation",
                    'type' => 'text',
                ],
                [
                    "label" => "Subsequent",
                    'type' => 'text',
                ],
                [
                    "label" => "Type",
                    'type' => 'text',
                ],
                [
                    "label" => "Evaluation",
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
            // throw $e;
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

            $df = DynamicForm::where('title', '=', 'DTE Employee Matrix - Original')->first();

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
