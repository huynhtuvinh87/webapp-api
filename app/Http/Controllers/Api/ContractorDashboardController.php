<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Contractor;
use App\Models\ExclusionRequest;
use App\Models\File;
use App\Models\Requirement;
use App\Models\RequirementHistory;
use App\Models\RequirementHistoryReview;
use App\Models\Role;
use App\Models\Test;
use App\Models\User;
use App\Traits\CacheTrait;
use App\Traits\ErrorHandlingTrait;
use App\Traits\FileTrait;
use App\ViewModels\ViewContractorResourceOverallCompliance;
use App\ViewModels\ViewEmployeeOverallCompliance;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContractorDashboardController extends Controller
{
    use FileTrait;
    use CacheTrait;
    use ErrorHandlingTrait;

    public function compliance(Request $request)
    {
        $contractor = null;
        $user = $request->user();
        $role = $user->role;
        $contractor = $role->company;
        if (get_class($contractor) != Contractor::class) {
            throw new Exception("Requesting user was not from a contractor.");
        }

        /** Overall compliance object for only corporate requirements*/
        $corporateRequirementCompliance = $user->role->company->overallCompliance;

        $resourceRequirementComplianceQuery = ViewContractorResourceOverallCompliance
            ::leftJoin("resources", "resources.id", "view_contractor_resource_overall_compliance.resource_id")
            ->where('resources.contractor_id', DB::raw($contractor->id))
            ->select([
                DB::raw("SUM(requirement_count) as requirement_count"),
                DB::raw("SUM(requirements_completed_count) as requirements_completed_count"),
            ]);

        $resourceRequirementCompliance = $resourceRequirementComplianceQuery->first();

        // Requirement Count => Corporate + Resources
        $requirement_count = $corporateRequirementCompliance->requirement_count + $resourceRequirementCompliance->requirement_count;
        // Completed Requirement Count => Corporate + Resources
        $requirements_completed_count = $corporateRequirementCompliance->requirements_completed_count + $resourceRequirementCompliance->requirements_completed_count;
        // Past Due Requirements => Corporate
        // NOTE: Resource pending requirements only show up in their lists, not the overall list
        // $requirements_past_due_count = $corporateRequirementCompliance->requirements_past_due_count + $resourceRequirementCompliance->pending_requirement_count;

        // NOTE: For some reason, the corporateRequirementCompliance->requirements_past_due_count is FUCKED.
        // Instead of trying to get that value, just going to use whats displayed in the list
        $pastDueHack = $this->pastDueRequirements($request);
        $requirements_past_due_count = sizeof(json_decode($pastDueHack->content())->past_due);

        return response([
            "requirement_count" => $requirement_count,
            "requirements_completed_count" => $requirements_completed_count,
            "requirements_past_due_count" => $requirements_past_due_count,
            "compliance" => round(($requirements_completed_count / $requirement_count) * 100),
        ]);
    }

    public function employeeCompliance(Request $request)
    {

        $employees = ViewEmployeeOverallCompliance
            ::where('view_employee_overall_compliance.entity_key', 'contractor')
            ->where('view_employee_overall_compliance.entity_id', $request->user()->role->entity_id)
            ->join('roles', 'roles.id', '=', 'view_employee_overall_compliance.role_id')
            ->where('roles.role', 'employee')
            ->get();

        $compliance = 0;

        if ($employees->count() !== 0) {
            $compliance = round($employees->sum('compliance') / $employees->count(), 0);
        }

        return response(
            [
                "employee_overall_compliance" => $compliance,
            ]
        );

    }

    public function companyCompliance(Request $request)
    {

        $user = $request->user();
        $contractor = $request->user()->role->company;

        $complianceByHiringOrg = $user->role->company->complianceByHiringOrganization()
            ->select([
                "contractor_id",
                DB::raw("null as resource_id"),
                "hiring_organization_id",
                "requirement_count",
                "requirements_completed_count",
                "name",
                DB::raw("null as resource_name"),
                DB::raw("'contractor' as type"),
            ]);
        $resourceComplianceByHiringOrg = $user->role->company->resourceComplianceByHiringOrganization()
            ->select([
                "contractor_id",
                "resource_id",
                "hiring_organization_id",
                "requirement_count",
                "requirements_completed_count",
                "hiring_organization_name as name",
                "resource_name",
                DB::raw("'resource' as type"),
            ]);

        $compliancesQuery = $complianceByHiringOrg
            ->union($resourceComplianceByHiringOrg);
        // NOTE: Sort on the frontend
        // ->orderBy("type", "asc")
        // ->orderBy("name", "desc")
        // ->orderBy("resource_name", "desc");

        return $compliancesQuery->get();

    }

    public function companyRequirements(Request $request, $id, $resource_id = null)
    {

        $user = $request->user();
        $locale = App::getLocale();
        return response([
            "compliance" => $user->role->company->complianceByHiringOrganizationPositions()->where('hiring_organization_id', $id)->get(),
            "requirements" => $user->role->company->requirements()
                ->where('hiring_organization_id', $id)
            // Enabling contractors to see internal documents. See DEV-1245
            // ->where('requirement_type', '!=', 'internal_document') // Internal Document should not be considered in Contractors side
                ->orderByRaw("FIELD(lang, '$locale') DESC")
                ->get()
                ->unique('requirement_id')
            // Adding values()->all() to convert object list back to an array
                ->values()->all(),
        ]);
    }

    public function requirementHistories(Request $request, Requirement $requirement)
    {

        $requirement = Requirement::find($requirement->id);

        $requirementHistories = DB::table("requirement_histories")
            ->leftJoin("requirement_history_reviews", "requirement_history_reviews.requirement_history_id", "requirement_histories.id")
            ->leftJoin("requirements", "requirements.id", "requirement_histories.requirement_id")
            ->leftJoin('requirement_contents', "requirement_contents.requirement_id", 'requirement_histories.requirement_id')
            ->leftJoin("file_requirement_history", "file_requirement_history.requirement_history_id", "requirement_histories.id")
            ->where("requirement_histories.requirement_id", DB::raw($requirement->id))
            ->where("requirement_histories.contractor_id", DB::raw($request->user()->role->company->id))
            ->select([
                DB::raw("GROUP_CONCAT(file_requirement_history.file_id SEPARATOR ',') as file_ids"),
                "requirement_histories.id as id",
                "requirement_histories.requirement_id",
                "requirement_contents.file_id as requirement_content_file_id",
                "requirement_histories.created_at",
                "requirement_histories.completion_date",
                "requirement_history_reviews.status",
                "requirement_history_reviews.status_at as status_date",
                DB::raw("NULL as exclusion_status"),
                "requirement_history_reviews.notes",
                "requirement_histories.resource_id",
                "requirement_contents.name as requirement_name",
                DB::raw("'history' as type"),
            ])
            ->groupBy(
                "requirement_histories.id",
                "requirement_contents.file_id",
                "requirement_history_reviews.status",
                "requirement_history_reviews.status_at",
                "requirement_history_reviews.notes",
                "requirement_histories.resource_id",
                "requirement_contents.name"
            );
        if ($request->has('resource_id')) {
            $requirementHistories->where("requirement_histories.resource_id", DB::raw($request->get('resource_id', null)));
        } else {
            $requirementHistories->whereNull("requirement_histories.resource_id");
        }

        $exclusionRequests = DB::table("exclusion_requests")
            ->where('exclusion_requests.requirement_id', DB::raw($requirement->id))
            ->where('exclusion_requests.contractor_id', DB::raw($request->user()->role->company->id))
            ->select([
                DB::raw("null as file_ids"),
                "exclusion_requests.id as id",
                DB::raw("null as requirement_id"),
                DB::raw("null as requirement_content_file_id"),
                "exclusion_requests.created_at",
                DB::raw("null as completion_date"),
                DB::raw("null as status"),
                "exclusion_requests.responded_at as status_date",
                "exclusion_requests.status as exclusion_status",
                "exclusion_requests.responder_note as notes",
                DB::raw("null as resource_id"),
                DB::raw("null as requirement_name"),
                DB::raw("'exclusion' as type"),
            ]);

        $historiesQuery = $requirementHistories
            ->union($exclusionRequests)
            ->orderBy('created_at', 'desc');

        $histories = $historiesQuery->get();

        foreach ($histories as $history) {

            //Get history files if history->type IS NOT exclusion request
            if($history->type != 'exclusion'){
            $historyObj = RequirementHistory::find($history->id);
            if (isset($historyObj)) {
                $files = $historyObj->files()->get();
                $history->file_ids = $files->map(function ($file) {
                    return [
                        'id' => $file->id,
                    ];
                })->toArray();
            }
            }
            try {
                $history->status_date = ($history->status_date) ? Carbon::parse($history->status_date)->format('Y-m-d') : null;
                $history->requirement_type = $requirement->type;

                if ($history->created_at > "1999-09-09 09:09:09") {
                    // fake date
                    $history->created_at = Carbon::parse($history->created_at)->format('Y-m-d');
                } else {
                    $history->created_at = "";
                }

                $history->renewal_date = ($history->completion_date)
                ? Carbon::createFromFormat('Y-m-d', $history->completion_date)->addMonths($requirement->renewal_period)->toDateString()
                : null;

            } catch (Exception $e) {
                Log::error($e, [
                    'history' => $history,
                ]);
            }
        }

        return response($histories);

    }

    public function pastDueRequirements(Request $request)
    {

        $user = $request->user();

        $requirements = $user->role->company->requirements()
            // Enabling contractors to see internal documents. See DEV-1245
            ->where('requirement_type', '!=', 'internal_document') // Internal Document should not be considered in Contractors side
            ->where(function ($query) use ($user) {
                $query->whereNull('exclusion_status')
                    ->orWhere('exclusion_status', '!=', 'approved');
            })
            ->get();
        $results = $requirements
            ->whereNotIn('status.status', ['completed', 'waiting'])
            ->unique('requirement_id')
            ->values()
            ->all();

        $resourceRequirementsQuery = $user->role->company->resourcePositionRequirements()
            ->where('requirement_type', '!=', 'internal_document') // Internal Document should not be considered in Contractors side
            ->where(function ($query) {
                $query->whereNull('exclusion_status')
                    ->orWhere('exclusion_status', '!=', 'approved');
            })
            ->get();

        $resource_results = $resourceRequirementsQuery
            ->whereNotIn('status.status', ['completed', 'waiting'])
            ->unique(function($record){
                return "$record->requirement_id - $record->resource_id";
            })
            ->values()
            ->all();

        foreach ($resource_results as $resource_position_requirement) {
            array_push($results, $resource_position_requirement);
        }

        return response(['past_due' => $results], 200);

    }

    public function aware(Request $request, $id)
    {

        Log::debug(__METHOD__, ['request' => $request]);
        $user = $request->user();

        $requirement = Requirement::find($id);
        if (!in_array($requirement->type, ['aware', 'aware_date', 'review', 'review_date'])) {
            return response(['error' => 'Wrong type of requirement'], 403);
        }

        // Determining renewal date: Now, or use the hard_deadline_date
        $completionDate = now();
        $hard_deadline_date = $requirement->hard_deadline_date;
        if ($hard_deadline_date) {
            $renewal_period = $requirement->renewal_period;
            $completionDate = Carbon::createFromFormat('Y-m-d', $hard_deadline_date)->subMonths($renewal_period)->format('Y-m-d');
        }
        $resource_id = $request->resource_id;
        $reqHistory = RequirementHistory::create([
            "requirement_id" => $id,
            "completion_date" => $completionDate,
            "role_id" => $user->current_role_id,
            "contractor_id" => $user->role->entity_id,
            "resource_id" => $resource_id,
        ]);

        $hiringOrg = $reqHistory->requirement->hiringOrganization;
        $role = $request->user()->role;
        Cache::tags([$this->getCompanyCacheTag($role), $this->getHiringOrgCacheTag($hiringOrg)])->flush();

        //save requirement history with "aware"
        return response(['message' => 'Success'], 200);

    }

    public function upload(Request $request, $id)
    {
        Log::debug(__METHOD__, ['request' => $request]);
        try {
            $requirement = Requirement::find($id);
            if(!isset($requirement)){
                throw new Exception("Requirement $id could not be found. Please refresh and try again.");
            }
            if ($requirement->type !== 'upload') {
                return response(['error' => 'Wrong type of requirement'], 403);
            }

            $user = $request->user();

            $filetypes = config('filetypes.documents_string');
            $maxFileSize = config('filesystems.max_size', 10240);

            // $this->validate($request, [
            //     "attachment" => "required|file|max:$maxFileSize|mimes:$filetypes"
            // ]);

            $file = $this->createFileFromRequest($request, 'attachment');

            // Determining renewal date: Now, or use the hard_deadline_date
            $completion_date = now();
            $hard_deadline_date = $requirement->hard_deadline_date;
            if ($hard_deadline_date) {
                $renewal_period = $requirement->renewal_period;
                $completion_date = Carbon::createFromFormat('Y-m-d', $hard_deadline_date)->subMonths($renewal_period)->format('Y-m-d');
            }

            $reqHistoryFileObj = new \stdClass;
            $reqHistoryFileObj->requirement_id = $requirement->id;
            $reqHistoryFileObj->completion_date = $completion_date;
            $reqHistoryFileObj->role_id = $user->current_role_id;
            $reqHistoryFileObj->contractor_id = $user->role->entity_id;
            $reqHistoryFileObj->file = $file;
            $reqHistoryFileObj->resource_id = $request->resource_id;
            $history = $this->createRequirementHistoryFile($reqHistoryFileObj);

            $hiringOrg = $requirement->hiringOrganization;
            $role = $request->user()->role;
            Cache::tags([$this->getCompanyCacheTag($role), $this->getHiringOrgCacheTag($hiringOrg)])->flush();

            return response($history);
        } catch (Exception $e) {
            Log::error($e);
            return response(['message' => $e->getMessage()], 400);
        }

    }

    public function uploadWithDate(Request $request, $id)
    {
        Log::debug(__METHOD__, ['request' => $request]);

        try {
            $requirement = Requirement::find($id);
            $resource_id = $request->resource_id;
            if ($requirement->type !== 'upload_date') {
                return response(['error' => 'Wrong type of requirement'], 403); //TODO translate this
            }

            $user = $request->user();

            $filetypes = config('filetypes.documents_string');
            $maxFileSize = config('filesystems.max_size', 10240);

            // $this->validate($request, [
            //     "attachment" => "required|file|max:$maxFileSize|mimes:$filetypes",
            //     "expiry-date" => "required|date"
            // ]);

            $file = $this->createFileFromRequest($request, 'attachment');

            $completion_date = Carbon::createFromFormat('Y-m-d', $request->get('expiry-date'))->subMonths($requirement->renewal_period);
            $expired = Carbon::createFromFormat('Y-m-d', $request->get('expiry-date'))->lessThan(now());

            if ($expired) {
                return response(['errors' => [
                    'certificate_expiry_date' => [
                        'Expiry date not valid', //TODO translate this
                    ],
                ]], 422); //TODO translate this
            }
            $reqHistoryFileObj = new \stdClass;
            $reqHistoryFileObj->requirement_id = $requirement->id;
            $reqHistoryFileObj->completion_date = $completion_date;
            $reqHistoryFileObj->role_id = $user->current_role_id;
            $reqHistoryFileObj->contractor_id = $user->role->entity_id;
            $reqHistoryFileObj->file = $file;
            $reqHistoryFileObj->resource_id = $resource_id;
            $history = $this->createRequirementHistoryFile($reqHistoryFileObj);

            $hiringOrg = $history->requirement->hiringOrganization;
            $role = $request->user()->role;
            Cache::tags([$this->getCompanyCacheTag($role), $this->getHiringOrgCacheTag($hiringOrg)])->flush();

            return response($history);

        } catch (Exception $e) {
            Log::error($e);
            return response(['message' => $e->getMessage()], 400);
        }

    }

    /**
     * TODO validate that the user is allowed to create this request, possibly queue creation, with alert failure
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function requestExclusion(Request $request)
    {

        $this->validate($request, [
            "note" => "string",
            "requirement_id" => "required|numeric|exists:requirements,id",
        ]);

        $user = $request->user();

        $requirement_id = $request->get('requirement_id');

        //ensure contractor has access to requirement
        if (!DB::table('view_contractor_requirements')
            ->where('contractor_id', $user->role->entity_id)
            ->where('requirement_id', $request->get('requirement_id'))
            ->exists()) {
            return response(['message' => 'Not Authorized'], 403);
        }

        // Can't request an exclusion for internal documents
        if (Requirement::find($requirement_id)->type == 'internal_document') {
            return response(['message' => 'Not authorized to request exclusions for internal documents'], 403);
        }

        //ensure contractor does not have existing valid or pending request
        if (DB::table('exclusion_requests')
            ->where('contractor_id', $user->role->entity_id)
            ->where('requirement_id', $request->get('requirement_id'))
            ->exists()) {

            return response([
                'errors' => [
                    'exclusion_request' => [
                        'An exclusion has already been requested for this requirement',
                    ],
                ],
            ], 422);

        }

        $exclusion_request = ExclusionRequest::create([
            "status" => "waiting",
            "requester_note" => $request->get('note'),
            "requirement_id" => $requirement_id,
            "requested_at" => now(),
            "requester_role_id" => $user->role->id,
            "contractor_id" => $user->role->entity_id,
        ]);

        $hiringOrg = Requirement::where('id', $requirement_id)->first()->hiringOrganization;
        $role = $request->user()->role;
        Cache::tags([$this->getCompanyCacheTag($role), $this->getHiringOrgCacheTag($hiringOrg)])->flush();

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

    }

    public function getExclusionRequest(Request $request, ExclusionRequest $exclusionRequest)
    {

        if ($exclusionRequest->contractor_id !== $request->user()->role->entity_id) {
            return response(['message' => 'Not authorized'], 403);
        }

        return response([
            'exclusion_request' => $exclusionRequest,
        ]);

    }

    public function deleteExclusionRequest(Request $request, ExclusionRequest $exclusionRequest)
    {

        if ($exclusionRequest->contractor_id !== $request->user()->role->entity_id) {
            return response(['message' => 'Not authorized'], 403);
        }

        $exclusionRequest->delete();

        return response(['message' => 'Success'], 200);
    }

    public function getRequirementTest(Request $request, Requirement $requirement)
    {

        if ($requirement->type !== 'test') {
            return response(['error' => 'Wrong type of requirement'], 407);
        }

        $test = Test::with('questions')->find($requirement->integration_resource_id);

        return response($test);

    }

    public function submitRequirementTest(Request $request, Requirement $requirement)
    {

        try {

            $test = $requirement->test;

            $questions = $test['questions'];

            $length = count($questions);

            $this->validate($request, [
                'answers' => 'required|array',
            ]);

            $success = false;

            $answers = $request->get('answers');

            $correct = 0;

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
                        'answer_text' => $answers[$question['id']],
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
                'status_at' => now(),
            ]);

            Cache::tags([$this->getCompanyCacheTag($request->user()->role), $this->getHiringOrgCacheTag($requirement->hiringOrganization)])->flush();
            return response(['pass' => $success, 'score' => $score, 'passing_score' => $test->min_passing_criteria]);

        } catch (Exception $e) {
            Log::error($e);
            return response(['message' => $e->getMessage()], 400);
        }

    }
}
