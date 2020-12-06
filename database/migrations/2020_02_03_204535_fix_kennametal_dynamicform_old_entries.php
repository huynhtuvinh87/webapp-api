<?php

use App\Models\DynamicFormSubmissionData;
use App\Models\Rating;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixKennametalDynamicformOldEntries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        $submissions = DB::table('dynamic_form_submission_data')
            ->join('dynamic_form_submissions', 'dynamic_form_submissions.id', '=', 'dynamic_form_submission_data.dynamic_form_submission_id')
            ->where('dynamic_form_submissions.dynamic_form_id', 19)
            ->where("dynamic_form_submission_data.dynamic_form_column_id", "=",  2662)
            ->oldest()
            ->get();

        // Fixing Dynamic Form Submission Data
        foreach ($submissions as $submission){
            $risk = DynamicFormSubmissionData::where("dynamic_form_submission_id", $submission->dynamic_form_submission_id)
                ->where('dynamic_form_column_label', 'Rating')
                ->first();

            $risk_score = DynamicFormSubmissionData::where("dynamic_form_submission_id", $submission->dynamic_form_submission_id)
                ->where('dynamic_form_column_label', 'Risk Score')
                ->first();

            if(!$risk_score){
                $risk_score_obj = [
                    'dynamic_form_submission_id' => $submission->dynamic_form_submission_id,
                    'dynamic_form_column_label' => 'Risk Score',
                    'dynamic_form_column_id' => 2773,
                    'value' => $this->getRiskString($risk->value)
                ];

                DynamicFormSubmissionData::create($risk_score_obj);

                Log::info("Updated risk score with " . $this->getRiskString($risk->value) . " for Rating  $risk->value");
            }

        }

        // Adding the comment for Ratings
        $rates = Rating::where('hiring_organization_id', 114)
            ->where('rating_system', 'form')
            ->whereNull('comments')
            ->latest()
            ->get();

        foreach ($rates as $rate){
            $rate->comments = $this->getRiskString($rate->rating);
            $rate->save();
            Log::info("Rating comment saved $rate->comments for $rate->rating");
        }

        Schema::enableForeignKeyConstraints();
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

    private function getRiskString($score){
        switch ($score) {
            case $score < 39:
                $string_risk = "Low Risk";
                break;
            case $score > 59:
                $string_risk = "High Risk";
                break;
            default:
                $string_risk = "Medium Risk";
                break;
        }

        return $string_risk;
    }
}
