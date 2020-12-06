<?php

use App\Models\DynamicForm;
use App\Models\DynamicFormSubmission;
use Illuminate\Database\Migrations\Migration;

class FixKMTRatingSubmissions extends Migration
{

    private $submissionsUpdatedCount = 0;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            DB::beginTransaction();

            // Get Dynamic form
            $form = DynamicForm::where(['id' => 19])->first();

            // Get submissions for form
            if (!isset($form)) {
                Log::warn("Form could not be found");
                return;
            }
            if ($form->title != "Kennametal Contractor Pre-Qualification Form") {
                throw new Exception("Unexpected form title: " . $form->title);
            }
            $formSubmissions = $form->submissions;
            if (!isset($formSubmissions)) {
                throw new Exception("No submissions found");
            }

            // Getting image column to be added
            $imageColumn = $form->columns
                ->where('type', 'image')
            // 62096 is the image that is to be displayed
                ->where('file_id', 62096)
                ->first();
            if (!isset($imageColumn)) {
                throw new Exception("Could not find image column");
            }

            // For each submission...
            $formSubmissions->each(function (DynamicFormSubmission $submission) use ($imageColumn) {
                $hasImageColumn = false;

                try {

                    // Check the submission stored form for the image column
                    $storedForm = $submission->storedDynamicForm();
                    if (!isset($storedForm)) {
                        throw new Exception("Stored form could not be found for submission " . $submission->id);
                    }

                    $imageColumns = $storedForm->columns
                        ->where('type', 'image')
                        ->where('file_id', 62096);

                    // If form is missing the new image, add it
                    if (sizeof($imageColumns) == 0) {

                        $storedForm->columns->push($imageColumn);
                        $newFormJSON = $storedForm->toJson();

                        // Validate JSON
                        json_decode($newFormJSON);
                        if (json_last_error() != JSON_ERROR_NONE) {
                            throw new Exception("Bad JSON");
                        }

                        // Storing form
                        $newFormJSON = json_encode(['form' => json_decode($newFormJSON)]);
                        $submission['dynamic_form_model'] = $newFormJSON;

                        // Log::debug("new form", ["form" => $newFormJSON]);

                        // Verifying
                        // Checking can be decoded
                        try {
                            $testForm = json_decode($submission['dynamic_form_model']);
                        } catch (Exception $e) {
                            throw new Exception("Could not decode test form");
                        }

                        // Checking if new json has form
                        try {
                            $testForm = json_decode($submission['dynamic_form_model'])->form;
                        } catch (Exception $e) {
                            Log::debug("Keys from dynamic_form_model. Should include 'form'", [
                                "Keys" => array_keys((array) json_decode($submission['dynamic_form_model'])),
                            ]);
                            throw new Exception("->form on test form was undefined");
                        }

                        // Checking for stored columns
                        try {
                            $testForm = json_decode($submission['dynamic_form_model'])->form->columns;
                        } catch (Exception $e) {
                            Log::debug("Keys from dynamic_form_model. Should include 'form->columns'", [
                            ]);
                            throw new Exception("form->columns on test form was undefined");
                        }

                        // Checking stored dynamic form
                        try {
                            $storedForm = $submission->storedDynamicForm();
                        } catch (Exception $e) {
                            Log::debug("submission->storedDynamicForm failed");
                            throw $e;
                        }

                        // Checking for ID
                        try {
                            $testForm = json_decode($submission['dynamic_form_model'])->form->id;
                        } catch (Exception $e) {
                            Log::debug("Keys from dynamic_form_model. Should include 'form->id'", [
                            ]);
                            throw new Exception("form->id on test form was undefined");
                        }

                        // Checking for Title
                        try {
                            $testForm = json_decode($submission['dynamic_form_model'])->form->title;
                        } catch (Exception $e) {
                            Log::debug("Keys from dynamic_form_model. Should include 'form->title'", [
                            ]);
                            throw new Exception("form->title on test form was undefined");
                        }

                        $submission->save();
                        $this->submissionsUpdatedCount += 1;

                    }
                } catch (Exception $e) {
                    Log::error("Could not save new form", [
                        'submission_id' => $submission->id,
                    ]);
                    Log::error($e->getMessage());
                    Log::debug("");
                }

            });

            Log::info("");
            Log::debug("Summary", [
                'submissionsUpdatedCount' => $this->submissionsUpdatedCount,
            ]);

            DB::commit();
        } catch (Exception $e) {
            Log::error($e);
            DB::rollback();
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
            DB::beginTransaction();

            DB::commit();
        } catch (Exception $e) {
            Log::error($e);
            DB::rollback();
            throw $e;
        }
    }
}
