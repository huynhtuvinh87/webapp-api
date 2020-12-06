<?php

use App\Models\DynamicForm;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

class FixForm89 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $formId = 89;

        $form = DynamicForm::find($formId);
        if(!isset($form)){
            Log::warn("Form 89 could not be found");
			if(config('app.env') != 'development'){
				throw new Exception("Form 89 could not be found");
			}
            return;
        }
        // Reset all columns in form 89

        DB::table("dynamic_form_columns")
            ->where("dynamic_form_id", $formId)
            ->delete();

        // Create new columns
        $formData = collect([
            [
                "label" => "Wheelchair",
                "description" => "",
                "type" => "radio",
                "order" => "1",
                "data" => '[{"label":"Yes","value":"Yes"},{"label":"No","value":"No"}]',
            ],
            [
                "label" => "License Plate",
                "description" => "",
                "type" => "text",
                "order" => "2",
                "data" => null,
            ],
            [
                "label" => "VIN",
                "description" => "Vehicle Insurance Number",
                "type" => "numeric",
                "order" => "3",
                "data" => null,
            ],
            [
                "label" => "Year",
                "description" => "",
                "type" => "numeric",
                "order" => "4",
                "data" => null,
            ],
            [
                "label" => "Make",
                "description" => "",
                "type" => "text",
                "order" => "5",
                "data" => null,
            ],
            [
                "label" => "Model",
                "description" => "",
                "type" => "text",
                "order" => "6",
                "data" => null,
            ],
            [
                "label" => "Color",
                "description" => "",
                "type" => "text",
                "order" => "7",
                "data" => null,
            ],
            [
                "label" => "Wheelchair Seats",
                "description" => "The number of",
                "type" => "numeric",
                "order" => "8",
                "data" => null,
            ],
            [
                "label" => "Car Seat Capable",
                "description" => "",
                "type" => "radio",
                "order" => "9",
                "data" => '[{"label":"Yes","value":"Yes"},{"label":"No","value":"No"}]',
            ],
            [
                "label" => "Class",
                "description" => "",
                "type" => "select",
                "order" => "10",
                "data" => '[{"label":"Van","value":"Van"},{"label":"Bus","value":"Bus"},{"label":"Sedan","value":"Sedan"},{"label":"Truck","value":"Truck"},{"label":"SUV","value":"SUV"}]',
            ],
            [
                "label" => "WC Capable",
                "description" => "",
                "type" => "radio",
                "order" => "11",
                "data" => '[{"label":"Rearload","value":"Rearload"},{"label":"Sideload","value":"Sideload"}]',
            ],
        ])
            ->map(function ($datagram) use ($formId) {
                $datagram['dynamic_form_id'] = $formId;
                return $datagram;
            })
            ->toArray();

        DB::table("dynamic_form_columns")
            ->insert($formData);
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
