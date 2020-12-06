<?php

use App\Models\Answer;
use App\Models\Requirement;
use App\Models\RequirementHistory;
use App\Models\RequirementHistoryReview;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

class FixTestsOldSubmissions extends Migration
{
    // 7 = Hiram Walker
    // 32 = Senior Aerospace
    // 39 = Dean Foods form
    // 34 = Idahoan
    // 24,25,27,28,30,23 = Sofina
    public $forms_to_be_changed = [7,32,39,34,24,25,27,28,30,23];

    /**
     * Run the migrations.
     *
     * @return void
     * @throws Exception
     */
    public function up()
    {
        try {
            // requirements completed by test
            $requirements = Requirement::where('type', 'test')->whereIn('integration_resource_id', $this->forms_to_be_changed)->get();

            // user to approve requirement
            $role_approver = User::where("email", "bot@contractorcompliance.io")->first()->role;

            $requirements_fixed = [];
            $requirements_review = [];

            foreach ($requirements as $requirement) {

                //requirement history
                $requirement_histories = RequirementHistory::where('requirement_id', $requirement->id)->get();

                //test
                $test = $requirement->test;

                //counting total questions for test
                $total_questions = count($test['questions']);

                foreach ($requirement_histories as $history) {

                    //count correct answers
                    $total_correct_answer = Answer::where('requirement_history_id', $history->id)->where('correct_answer', 1)->count();

                    if (is_null($total_correct_answer)) {
                        throw new Exception("No answers found for requirement $requirement->id");
                    }

                    //percentage of correct answers
                    $score = round($total_correct_answer / $total_questions * 100);

                    //requirement status based on correct answers
                    $status = ($score >= $test->min_passing_criteria) ? "approved" : "declined";

                    // create requirement history review which will determine requirement status
                    $history_review = RequirementHistoryReview::create([
                        'requirement_history_id' => $history->id,
                        'approver_id' => $role_approver->id,
                        'status' => $status,
                        'notes' => ($status == "approved")
                            ? "Scored $score% in the test, $test->min_passing_criteria% is the passing mark. Requirement Approved."
                            : "Scored $score% in the test, $test->min_passing_criteria% is the passing mark. Requirement Declined.",
                        'status_at' => "2020-02-24 00:00:00"
                    ]);

                    // log
                    $requirements_fixed[] = $history->requirement_id;
                    $requirements_review[] = $history_review->id;

                }
            }

//            Log::debug("Fixed following requirements");
//            Log::debug(json_encode($requirements_fixed));
//            Log::debug(json_encode($requirements_review));

        } catch (Exception $exception){
            Log::error($exception);
        }
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
