<?php

use App\Models\DynamicForm;
use Illuminate\Database\Migrations\Migration;

class FixForm88 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $formId = 88;
        $form = DynamicForm::find($formId);
        if(!isset($form)){
            Log::warn("Form $formId as not found. Not adding columns");
            return null;
        }

        // Reset all columns in form 88
        $oldFormColumns = DB::table("dynamic_form_columns")
            ->where("dynamic_form_id", $formId);

        if ($oldFormColumns) {
            $oldFormColumns->delete();
        }

        // Create new columns
        $formData = collect([
            [
                "label" => "Physical Address",
                "description" => "Can't be PO box",
                "type" => "label",
                "order" => "1",
                "data" => null,
            ],
            [
                "label" => "Address Line 1",
                "description" => "",
                "type" => "text",
                "order" => "2",
                "data" => null,
            ],
            [
                "label" => "Address Line 2",
                "description" => "",
                "type" => "text",
                "order" => "3",
                "data" => null,
            ],
            [
                "label" => "City",
                "description" => "",
                "type" => "text",
                "order" => "4",
                "data" => null,
            ],
            [
                "label" => "State",
                "description" => "",
                "type" => "select",
                "order" => "5",
                "data" => '[{"label":"Alabama","value":"Alabama"},{"label":"Alaska","value":"Alaska"},{"label":"Arizona","value":"Arizona"},{"label":"Arkansas","value":"Arkansas"},{"label":"California","value":"California"},{"label":"Colorado","value":"Colorado"},{"label":"Connecticut","value":"Connecticut"},{"label":"Delaware","value":"Delaware"},{"label":"Florida","value":"Florida"},{"label":"Georgia","value":"Georgia"},{"label":"Hawaii","value":"Hawaii"},{"label":"Idaho","value":"Idaho"},{"label":"Illinois","value":"Illinois"},{"label":"Indiana","value":"Indiana"},{"label":"Iowa","value":"Iowa"},{"label":"Kansas","value":"Kansas"},{"label":"Kentucky","value":"Kentucky"},{"label":"Louisiana","value":"Louisiana"},{"label":"Maine","value":"Maine"},{"label":"Maryland","value":"Maryland"},{"label":"Massachusetts","value":"Massachusetts"},{"label":"Michigan","value":"Michigan"},{"label":"Minnesota","value":"Minnesota"},{"label":"Mississippi","value":"Mississippi"},{"label":"Missouri","value":"Missouri"},{"label":"Montana","value":"Montana"},{"label":"Nebraska","value":"Nebraska"},{"label":"Nevada","value":"Nevada"},{"label":"New Hampshire","value":"New Hampshire"},{"label":"New Jersey","value":"New Jersey"},{"label":"New Mexico","value":"New Mexico"},{"label":"New York","value":"New York"},{"label":"North Carolina","value":"North Carolina"},{"label":"North Dakota","value":"North Dakota"},{"label":"Ohio","value":"Ohio"},{"label":"Oklahoma","value":"Oklahoma"},{"label":"Oregon","value":"Oregon"},{"label":"Pennsylvania","value":"Pennsylvania"},{"label":"Rhode Island","value":"Rhode Island"},{"label":"South Carolina","value":"South Carolina"},{"label":"South Dakota","value":"South Dakota"},{"label":"Tennessee","value":"Tennessee"},{"label":"Texas","value":"Texas"},{"label":"Utah","value":"Utah"},{"label":"Vermont","value":"Vermont"},{"label":"Virginia","value":"Virginia"},{"label":"Washington","value":"Washington"},{"label":"West Virginia","value":"West Virginia"},{"label":"Wisconsin","value":"Wisconsin"},{"label":"Wyoming","value":"Wyoming"}]',
            ],
            [
                "label" => "Postal Code",
                "description" => "",
                "type" => "text",
                "order" => "6",
                "data" => null,
            ],
            [
                "label" => "Mailing Address",
                "description" => "",
                "type" => "label",
                "order" => "7",
                "data" => null,
            ],
            [
                "label" => "Address Line 1",
                "description" => "",
                "type" => "text",
                "order" => "8",
                "data" => null,
            ],
            [
                "label" => "Address Line 2",
                "description" => "",
                "type" => "text",
                "order" => "9",
                "data" => null,
            ],
            [
                "label" => "City",
                "description" => "",
                "type" => "text",
                "order" => "10",
                "data" => null,
            ],
            [
                "label" => "State",
                "description" => "",
                "type" => "select",
                "order" => "11",
                "data" => '[{"label":"Alabama","value":"Alabama"},{"label":"Alaska","value":"Alaska"},{"label":"Arizona","value":"Arizona"},{"label":"Arkansas","value":"Arkansas"},{"label":"California","value":"California"},{"label":"Colorado","value":"Colorado"},{"label":"Connecticut","value":"Connecticut"},{"label":"Delaware","value":"Delaware"},{"label":"Florida","value":"Florida"},{"label":"Georgia","value":"Georgia"},{"label":"Hawaii","value":"Hawaii"},{"label":"Idaho","value":"Idaho"},{"label":"Illinois","value":"Illinois"},{"label":"Indiana","value":"Indiana"},{"label":"Iowa","value":"Iowa"},{"label":"Kansas","value":"Kansas"},{"label":"Kentucky","value":"Kentucky"},{"label":"Louisiana","value":"Louisiana"},{"label":"Maine","value":"Maine"},{"label":"Maryland","value":"Maryland"},{"label":"Massachusetts","value":"Massachusetts"},{"label":"Michigan","value":"Michigan"},{"label":"Minnesota","value":"Minnesota"},{"label":"Mississippi","value":"Mississippi"},{"label":"Missouri","value":"Missouri"},{"label":"Montana","value":"Montana"},{"label":"Nebraska","value":"Nebraska"},{"label":"Nevada","value":"Nevada"},{"label":"New Hampshire","value":"New Hampshire"},{"label":"New Jersey","value":"New Jersey"},{"label":"New Mexico","value":"New Mexico"},{"label":"New York","value":"New York"},{"label":"North Carolina","value":"North Carolina"},{"label":"North Dakota","value":"North Dakota"},{"label":"Ohio","value":"Ohio"},{"label":"Oklahoma","value":"Oklahoma"},{"label":"Oregon","value":"Oregon"},{"label":"Pennsylvania","value":"Pennsylvania"},{"label":"Rhode Island","value":"Rhode Island"},{"label":"South Carolina","value":"South Carolina"},{"label":"South Dakota","value":"South Dakota"},{"label":"Tennessee","value":"Tennessee"},{"label":"Texas","value":"Texas"},{"label":"Utah","value":"Utah"},{"label":"Vermont","value":"Vermont"},{"label":"Virginia","value":"Virginia"},{"label":"Washington","value":"Washington"},{"label":"West Virginia","value":"West Virginia"},{"label":"Wisconsin","value":"Wisconsin"},{"label":"Wyoming","value":"Wyoming"}]',
            ],
            [
                "label" => "Postal Code",
                "description" => "",
                "type" => "text",
                "order" => "12",
                "data" => null,
            ],
            [
                "label" => "DOB",
                "description" => "Date of Birth",
                "type" => "label",
                "order" => "13",
                "data" => null,
            ],
            [
                "label" => "Month",
                "description" => "",
                "type" => "numeric",
                "order" => "14",
                "data" => null,
            ],
            [
                "label" => "Day",
                "description" => "",
                "type" => "numeric",
                "order" => "15",
                "data" => null,
            ],
            [
                "label" => "Year",
                "description" => "",
                "type" => "numeric",
                "order" => "16",
                "data" => null,
            ],
            [
                "label" => "SSN",
                "description" => "Social Security Number",
                "type" => "numeric",
                "order" => "17",
                "data" => null,
            ],
            [
                "label" => "Phone Number",
                "description" => "",
                "type" => "numeric",
                "order" => "18",
                "data" => null,
            ],
            [
                "label" => "Hair color",
                "description" => "",
                "type" => "radio",
                "order" => "19",
                "data" => '[{"label":"Brown","value":"Brown"},{"label":"Black","value":"Black"},{"label":"Gray","value":"Gray"},{"label":"White","value":"White"},{"label":"Blonde","value":"Blonde"},{"label":"Bald","value":"Bald"},{"label":"Orange","value":"Orange"},{"label":"Blue","value":"Blue"},{"label":"Green","value":"Green"}]',
            ],

            [
                "label" => "Eye Color",
                "description" => "",
                "type" => "label",
                "order" => "20",
                "data" => '[{"label":"Black","value":"Black"},{"label":"Brown","value":"Brown"},{"label":"Green","value":"Green"},{"label":"Blue","value":"Blue"},{"label":"Hazel","value":"Hazel"},{"label":"Orange","value":"Orange"},{"label":"Red","value":"Red"}]',
            ],

            [
                "label" => "Height",
                "description" => "",
                "type" => "label",
                "order" => "21",
                "data" => null,
            ],
            [
                "label" => "Feet",
                "description" => "",
                "type" => "numeric",
                "order" => "22",
                "data" => null,
            ],
            [
                "label" => "Inches",
                "description" => "",
                "type" => "numeric",
                "order" => "23",
                "data" => null,
            ],
            [
                "label" => "Weight",
                "description" => "",
                "type" => "label",
                "order" => "24",
                "data" => null,
            ],
            [
                "label" => "Pounds",
                "description" => "",
                "type" => "numeric",
                "order" => "25",
                "data" => null,
            ],
            [
                "label" => "Gender",
                "description" => "",
                "type" => "radio",
                "order" => "26",
                "data" => '[{"label":"Male","value":"Male"},{"label":"Female","value":"Female"}]',
            ],

            [
                "label" => "Emergency Contact",
                "description" => "",
                "type" => "label",
                "order" => "27",
                "data" => null,
            ],
            [
                "label" => "First Name",
                "description" => "",
                "type" => "text",
                "order" => "28",
                "data" => null,
            ],
            [
                "label" => "Last Name",
                "description" => "",
                "type" => "text",
                "order" => "29",
                "data" => null,
            ],
            [
                "label" => "Phone Number",
                "description" => "",
                "type" => "numeric",
                "order" => "30",
                "data" => null,
            ],
            [
                "label" => "Relation",
                "description" => "",
                "type" => "text",
                "order" => "31",
                "data" => null,
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
