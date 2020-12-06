<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\ExclusionRequest;
use App\Models\Requirement;
use App\Models\RequirementHistory;
use App\Models\RequirementHistoryReview;
use App\Models\FileRequirementHistory;
use App\Models\File;
use App\Models\Resource;
use App\Models\Role;
use App\Models\Test;
use App\Models\User;
use App\Traits\CacheTrait;
use App\Traits\FileTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Controller for employee dashboard
 *
 * Class EmployeeController
 * @package App\Http\Controllers\Api
 */
class EmployeeDashboardController extends Controller
{
    use FileTrait;
    use CacheTrait;

    public function compliance(Request $request)
    {

        $user = $this->userContext($request);

        if ($user === false) {
            return response('Not Authorized', 403);
        }

        return response($user->role->overallCompliance);
    }

    public function companyCompliance(Request $request)
    {

        $user = $this->userContext($request);

        if ($user === false) {
            return response('Not Authorized', 403);
        }

        return response($user->role->complianceByHiringOrganization);

    }

    public function companyRequirements(Request $request, $id)
    {

        $user = $this->userContext($request);

        if ($user === false) {
            return response('Not Authorized', 403);
        }

        $locale = App::getLocale();

        return response([
            "compliance" => $user->role->complianceByHiringOrganizationPositions()->where('hiring_organization_id',
                $id)->get(),
            "requirements" => $user->role->requirements()
                ->where('hiring_organization_id', $id)
                // Enabling contractors to see internal documents. See DEV-1245
                // ->where('requirement_type', '!=', 'internal_document') // Internal Document should not be considered in Employees side
                ->orderByRaw("FIELD(lang, '$locale') DESC")
                ->get()
                ->unique('requirement_id')
                ->values()->all()
        ]);

    }

    public function requirementHistories(Request $request, $id, $role_id)
    {

        $user = $this->userContext($request);

        if ($user === false) {
            return response('Not Authorized', 403);
        }

        $requirement = Requirement::find($id);

        $query = "
            SELECT
                *
            FROM
            (
            SELECT distinct GROUP_CONCAT(rhf.file_id SEPARATOR ',') as 'file_ids',
                rh.id,
                rc.name as requirement_name,
                rh.requirement_id,
                rh.created_at,
                rh.completion_date,
                rhr.status,
                rhr.status_at as status_date,
                null as exclusion_status,
                rhr.notes,
                'history' as `type`
            FROM
                requirement_histories rh
            LEFT JOIN
                requirement_history_reviews rhr ON rh.id = rhr.requirement_history_id
            LEFT JOIN
                requirements rq ON rq.id = rh.requirement_id
            LEFT JOIN
                requirement_contents rc ON rc.requirement_id = rq.id
            LEFT JOIN file_requirement_history rhf ON rh.id = rhf.requirement_history_id 
                WHERE
                rh.requirement_id = ".$requirement->id." AND rh.role_id = ".$role_id."
                GROUP BY rh.id, rc.name, rhr.status_at, rhr.status, rhr.notes 
                UNION
            SELECT
                null as id,
                null as requirement_name,
                null as requirement_id,
                null as file_id,
                created_at,
                null as completion_date,
                null as status,
                er.responded_at as status_date,
                er.status as exclusion_status,
                er.responder_note as notes,
                'exclusion' as 'type'
            FROM
                exclusion_requests er
            WHERE
                er.requirement_id = ".$requirement->id." AND er.requester_role_id = ".$role_id."
            ) AS history
            ORDER BY
                created_at DESC,
                status_date DESC,
                requirement_id DESC";

$histories = DB::select($query);

        foreach ($histories as $history) {
            //Get history files
            $historyObj = RequirementHistory::find($history->id);
            if (isset($historyObj)) {
                $files = $historyObj->files()->get();
                $history->file_ids = $files->map(function ($file) {
                    return [
                        'id' => $file->id,
                    ];
                })->toArray();
            }
            //Date formatting
            try {
            $history->status_date = ($history->status_date) ? Carbon::parse($history->status_date)->format('Y-m-d') : null;
            $history->requirement_type = $requirement->type;

            if ($history->created_at > "1999-09-09 09:09:09") { // fake date
                $history->created_at = Carbon::parse($history->created_at)->format('Y-m-d');
            } else {
                $history->created_at = "";
            }

            $history->renewal_date = ($history->completion_date)
                ? Carbon::createFromFormat('Y-m-d', $history->completion_date)->addMonths($requirement->renewal_period)->toDateString()
                : NULL;

            } catch (Exception $e) {
                Log::error($e,[
                    'history' => $history
                ]);
                return response(['message' => $e->getMessage()], 400);
            }
        }

        return response($histories);

    }

    public function pastDueRequirements(Request $request)
    {

        $user = $this->userContext($request);

        if ($user === false) {
            return response('Not Authorized', 403);
        }

        $locale = App::getLocale();

        $requirements = $user->role->requirements()
            // Enabling contractors to see internal documents. See DEV-1245
            // ->where('requirement_type', '!=', 'internal_document') // Internal Document should not be considered in Employees side
            ->where(function ($query) use ($user){
                $query->whereNull('exclusion_status')
                    ->orWhere('exclusion_status', '!=', 'approved');
            })
            ->get();
        $results = $requirements->whereNotIn('status.status', ['completed', 'waiting'])->unique('requirement_id')->values()->all();
        return response(['past_due' => $results], 200);
    }

    public function aware(Request $request, $id)
    {

        try {
            $user = $request->user();
            $requirement = Requirement::find($id);

            if (!in_array($requirement->type, ['aware', 'aware_date', 'review', 'review_date'])) {
                return response(['error' => 'Wrong type of requirement'], 407);
            }

            if (!isset($user->role->entity_key) || $user->role->entity_key != 'contractor') {
                throw new Exception("Role not found or cannot perform this action");
            }

            // Determining renewal date: Now, or use the hard_deadline_date
            $completionDate = now();
            $hard_deadline_date = $requirement->hard_deadline_date;
            if ($hard_deadline_date) {
                $renewal_period = $requirement->renewal_period;
                $completionDate = Carbon::createFromFormat('Y-m-d', $hard_deadline_date)->subMonths($renewal_period)->format('Y-m-d');
            }

            RequirementHistory::create([
                "requirement_id" => $id,
                "completion_date" => now(),
                "role_id" => $user->current_role_id,
                "contractor_id" => $user->role->entity_id,
            ]);
            //save requirement history with "aware"
            return response(['message' => 'ok'], 200);
        } catch (Exception $exception) {
            Log::error($exception);
            return response(['message' => $exception->getMessage()], 400);
        }

    }

    public function upload(Request $request, $id)
    {

        try {
            $user = $this->userContext($request);
            $requirement = Requirement::find($id);
            if ($requirement->type !== 'upload') {
                return response(['error' => 'Wrong type of requirement'], 407);
            }

            if ($user === false) {
                return response('Not Authorized', 403);
            }

            $filetypes = config('filetypes.documents_string');
            $maxFileSize = config('filesystems.max_size', 10240);
            //$attachments = $request->files();
            // $this->validate($request, [
            //     "attachment" => "required|file|max:$maxFileSize|mimes:$filetypes"
            // ]);

            
            $file = $this->createFileFromRequest($request, 'attachment');
            if (!isset($user->role->entity_key) || $user->role->entity_key != 'contractor') {
                throw new Exception("Role not found or cannot perform this action");
            }

            // Determining renewal date: Now, or use the hard_deadline_date
            $completion_date = now();
            $hard_deadline_date = $requirement->hard_deadline_date;
            if ($hard_deadline_date) {
                $renewal_period = $requirement->renewal_period;
                $completion_date = Carbon::createFromFormat('Y-m-d', $hard_deadline_date)->subMonths($renewal_period)->format('Y-m-d');
            }
            
            $reqHistoryFileObj = new \stdClass;
            $reqHistoryFileObj->requirement_id = $id;
            $reqHistoryFileObj->completion_date = $completion_date;
            $reqHistoryFileObj->role_id = $user->role->id;
            $reqHistoryFileObj->contractor_id = $user->role->entity_id;
            $reqHistoryFileObj->file = $file;
            $history = $this->createRequirementHistoryFile($reqHistoryFileObj);

            // Iterate the array if more than one file uploaded
            // Else use the file returned to create a requirement history file
            

            //save requirement history with attachment
            return response(['message' => 'ok'], 200);
        } catch (Exception $exception) {
            Log::error($exception);
            return response(['message' => $exception->getMessage()], 400);
        }

    }

    public function uploadWithDate(Request $request, $id)
    {

        try {
            $user = $this->userContext($request);

            $requirement = Requirement::find($id);

            if ($requirement->type !== 'upload_date') {
                return response(['error' => 'Wrong type of requirement'], 407);
            }

            if ($user === false) {
                return response('Not Authorized', 403);
            }

            $filetypes = config('filetypes.documents_string');
            $maxFileSize = config('filesystems.max_size', 10240);

            // $this->validate($request, [
            //     "attachment" => "required|file|max:$maxFileSize|mimes:$filetypes",
            //     "expiry-date" => "required|date"
            // ]);

            $file = $this->createFileFromRequest($request, 'attachment');

            $completion_date = Carbon::createFromFormat('Y-m-d',
                $request->get('expiry-date'))->subMonths($requirement->renewal_period);
            $expired = Carbon::createFromFormat('Y-m-d', $request->get('expiry-date'))->lessThan(now());

            if ($expired) {
                return response([
                    'errors' => [
                        'certificate_expiry_date' => [
                            'Expiry date not valid' //TODO translate this
                        ]
                    ]
                ], 422); //TODO translate this
            }

            if (!isset($user->role->entity_key) || $user->role->entity_key != 'contractor') {
                throw new Exception("Role not found or cannot perform this action");
            }
            $reqHistoryFileObj = new \stdClass;
            $reqHistoryFileObj->requirement_id = $id;
            $reqHistoryFileObj->completion_date = $completion_date;
            $reqHistoryFileObj->role_id = $user->role->id;
            $reqHistoryFileObj->contractor_id = $user->role->entity_id;
            $reqHistoryFileObj->file = $file;
            $history = $this->createRequirementHistoryFile($reqHistoryFileObj);
            
            // RequirementHistory::create([
            //     "requirement_id" => $id,
            //     "completion_date" => $completion_date,
            //     "role_id" => $user->role->id,
            //     "file_id" => $file->id,
            //     "contractor_id" => $user->role->entity_id,
            // ]);

            return response(['message' => 'ok'], 200);
        } catch (Exception $exception) {
            Log::error($exception);
            return response(['message' => $exception->getMessage()], 400);
        }

    }

    /**
     * TODO validate that the user is allowed to create this request, possibly queue creation, with alert failure
     * @param  Request  $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function requestExclusion(Request $request)
    {

        try {
            $this->validate($request, [
                "note" => "max:255",
                "requirement_id" => "required|numeric|exists:requirements,id"
            ]);

            $user = $this->userContext($request);

            if ($user === false) {
                return response('Not Authorized', 403);
            }

            //ensure user has access to requirement
            if (!DB::table('view_employee_requirements')
                ->where('user_id', $user->id)
                ->where('requirement_id', $request->get('requirement_id'))
                ->exists()) {
                return response(['message' => 'Not Authorized'], 403);
            }

            //ensure user has no waiting or approved exclusion requests
            if (DB::table('exclusion_requests')
                ->where('requester_role_id', $user->role->id)
                ->where('requirement_id', $request->get('requirement_id'))
                ->exists()) {

                return response([
                    'errors' => [
                        'exclusion_request' => [
                            'An exclusion has already been requested for this requirement'
                        ]
                    ]
                ], 422);

            }

            $requirement_id = $request->get('requirement_id');

            if (!isset($user->role->entity_key) || $user->role->entity_key != 'contractor') {
                throw new Exception("Role not found or cannot perform this action");
            }

            $exclusion_request = ExclusionRequest::create([
                "status" => "waiting",
                "requester_note" => $request->get('note'),
                "requirement_id" => $requirement_id,
                "requested_at" => now(),
                "requester_role_id" => $user->role->id,
                "contractor_id" => $user->role->entity_id
            ]);

            dispatch(function () use ($requirement_id) {

                $requirement = Requirement::find($requirement_id);

                $user_ids = DB::table('users')
                    ->join('roles', 'roles.user_id', '=', 'users.id')
                    ->join('department_role', 'roles.id', '=', 'department_role.role_id')
                    ->join('departments', 'departments.id', '=', 'department_role.department_id')
                    ->join('department_requirement', 'departments.id', '=', 'department_requirement.department_id')
                    ->where('department_requirement.requirement_id', $requirement_id)
                    ->pluck('users.id')
                    ->toArray();

                /*foreach($user_ids as $user_id){
                    \App\Jobs\SystemNotification::dispatch(
                        'New exclusion request for '.$requirement->name,
                        $user_id,
                        null,
                        config('client.web_ui').'/organization/dashboard', //TODO update link to exclusion request dashboard
                        'Dashboard'
                    );
                }*/

            });

            return response($exclusion_request);

        } catch (Exception $exception) {
            Log::error($exception);
            return response(['message' => $exception->getMessage()], 400);
        }
    }

    public function getExclusionRequest(Request $request, ExclusionRequest $exclusionRequest)
    {

        $user = $this->userContext($request);

        if ($exclusionRequest->requester_role_id !== $user->role->id) {
            return response(['message' => 'Not authorized'], 403);
        }

        return response([
            'exclusion_request' => $exclusionRequest
        ]);

    }

    public function deleteExclusionRequest(Request $request, ExclusionRequest $exclusionRequest)
    {

        $user = $this->userContext($request);

        if ($exclusionRequest->requester_role_id !== $user->role->id) {
            return response(['message' => 'Not authorized'], 403);
        }

        $exclusionRequest->delete();

        return response(['message' => 'ok'], 200);
    }

    public function getRequirementTest(Request $request, Requirement $requirement)
    {

        if ($requirement->type !== 'test') {
            return response(['message' => 'Wrong type of requirement'], 407);
        }

        $test = Test::with([
            'questions' => function ($query) {
                $query->select('id', 'test_id', 'question_text', 'reference', 'option_1', 'option_2', 'option_3',
                    'option_4');
            }
        ])->find($requirement->integration_resource_id);

        if (!$test) {
            return response(['message' => 'Test '.$requirement->test_id.' was not found'], 404);
        }

        //TODO limit max tries
        $max = $test->max_tries;

        if ($request->user()->role->role === 'employee') {

            $attempts = RequirementHistory::where('requirement_id', $requirement->id)->where('role_id',
                $request->user()->role->id)->count();

        } else {
            $attempts = RequirementHistory::where('requirement_id', $requirement->id)->where('contractor_id',
                $request->user()->role->entity_id)->count();
        }

        if ($attempts >= $max) {
            return response(['message' => 'Too Many Attempts'], 403);
        }

        return response($test);

    }

    public function submitRequirementTest(Request $request, Requirement $requirement)
    {

        try {

            $test = $requirement->test;

            $questions = $test['questions'];

            $length = count($questions);

            $this->validate($request, [
                'answers' => 'required|array'
            ]);

            $success = false;

            $answers = $request->get('answers');

            $correct = 0;

            if (!isset($request->user()->role->entity_key) || $request->user()->role->entity_key != 'contractor') {
                throw new Exception("Role not found or cannot perform this action");
            }

            $history = RequirementHistory::create([
                "requirement_id" => $requirement->id,
                "completion_date" => now(),
                "role_id" => $request->user()->current_role_id,
                "contractor_id" => $request->user()->role->entity_id,
            ]);

            foreach ($questions as $question) {
                $match = 0;
                if (array_key_exists($question['id'],
                        $answers) && $answers[$question['id']] === $question['correct_answer']) {
                    $correct += 1;
                    $match = 1;
                    Answer::create([
                        'requirement_history_id' => $history->id,
                        'question_id' => $question['id'],
                        'correct_answer' => $match,
                        'answer_text' => $answers[$question['id']]
                    ]);
                }
            }

            $score = round($correct / $length * 100);

            if ($score >= $test->min_passing_criteria) {
                $success = true;
            }

            $history->valid = $success;
            $history->save();

            $role_approver = User::where("email", "bot@contractorcompliance.io")->first()->role;

            $history_review = RequirementHistoryReview::create([
                'requirement_history_id' => $history->id,
                'approver_id' => $role_approver->id,
                'status' => ($success) ? "approved" : "declined",
                'notes' => ($success)
                    ? "Scored $score% in the test, $test->min_passing_criteria% is the passing mark. Requirement Approved."
                    : "Scored $score% in the test, $test->min_passing_criteria% is the passing mark. Requirement Declined.",
                'status_at' => now()
            ]);

            Cache::tags([$this->getCompanyCacheTag($request->user()->role), $this->getHiringOrgCacheTag($requirement->hiringOrganization)])->flush();
            return response(['pass' => $success, 'score' => $score, 'passing_score' => $test->min_passing_criteria]);

        } catch (Exception $exception) {
            Log::error($exception);
            return response(['message' => $exception->getMessage()], 400);
        }

    }

    public function getRequirementContent(Request $request, Requirement $requirement)
    {

        if (
        !DB::table('contractor_hiring_organization')
            ->where('contractor_id', $request->user()->role->entity_id)
            ->where('hiring_organization_id', $requirement->hiring_organization_id)
            ->exists()
        ) {
            return response(['error' => 'Not authorized'], 403);
        }

        return response([
            'content' => $requirement->localizedContent()
        ]);
    }

    /**
     * If employee_id is provided, ensure user is admin and the employee is of the same organization,
     * all admin to implement routes
     * @param $request
     * @return User|bool
     */
    private function userContext($request)
    {

        if ($request->query('employee_id') && is_numeric($request->query('employee_id'))) {
            $role = $request->user()->role;

            $employee_role = Role::where('user_id', $request->query('employee_id'))
                ->where('entity_key', 'contractor')
                ->where('entity_id', $role->entity_id)
                ->where('role', 'employee')
                ->first();

            if (
                ($role->role !== "admin" && $role->role !== "owner") ||
                (!$employee_role)
            ) {
                return false;
            }

            $user = User::find($request->get('employee_id'));

            $user->role = $employee_role;

            return $user;

        }

        return $request->user();

    }

}
