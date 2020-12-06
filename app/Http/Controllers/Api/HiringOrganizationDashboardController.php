<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Lib\Services\HiringOrganizationCompliance;
use App\Lib\Services\HiringOrganizationComplianceV2;
use App\Models\Contractor;
use App\Models\ExclusionRequest;
use App\Models\HiringOrganization;
use App\Models\Position;
use App\Models\Requirement;
use App\Models\RequirementHistory;
use App\Models\RequirementHistoryReview;
use App\Models\Resource;
use App\Models\Role;
use App\Models\User;
use App\Notifications\Requirement\Declined;
use App\Notifications\Requirement\ExclusionApproved;
use App\Notifications\Requirement\ExclusionDeclined;
use App\Traits\CacheTrait;
use App\Traits\FileTrait;
use App\ViewModels\ViewContractorRequirements;
use App\ViewModels\ViewEmployeeRequirements;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Log;

class HiringOrganizationDashboardController extends Controller
{
    use FileTrait;
    use CacheTrait;

    public function overallCompliance(Request $request): Response
    {
        $contractorCompliance = 0;
        $employeeCompliance = 0;
        $contractors = null;

        $role = $request->user()->role;
        $hiringOrg = $role->company;

        if(get_class($hiringOrg) != HiringOrganization::class){
            throw new Exception("Role was not for a hiring organization. Can't obtain compliance");
        }

        $filters = $this->getRequestFilters($request);

        $compliances = HiringOrganizationComplianceV2::getOverallComplianceQuery($hiringOrg)->get();
        $compliances
            ->each(function ($compliance) use (&$contractorCompliance, &$employeeCompliance) {
                switch ($compliance->position_type) {
                    case 'contractor':
                        $contractorCompliance = $compliance->compliance;
                        break;
                    case 'employee':
                        $employeeCompliance = $compliance->compliance;
                        break;
                }
            });

        $contractors = HiringOrganizationComplianceV2::getContractorsWithCompliance($role, $hiringOrg, $filters);

        return response([
            'contractor_compliance' => $contractorCompliance,
            'employee_compliance' => $employeeCompliance,
            'contractors' => $contractors,
        ]);
    }

    public function pendingContractorRequirements(Request $request): Response
    {
        $role = $request->user()->role;
        $hiringOrg = $role->company;

        $pendingReqTag = $this->buildTag("pending-corporate-requirements", $hiringOrg->id);
        $tags = $this->buildTagsFromRequest($request, [$pendingReqTag]);
        $key = $this->buildKeyFromRequest($request);

        $response = Cache::tags($tags)->remember($key, config('cache.time'), function () use ($request, $hiringOrg, $role) {

            if (get_class($hiringOrg) != HiringOrganization::class) {
                throw new Exception("Role was not for a hiring organization. Can't obtain compliance");
            }

            $requirements = HiringOrganizationComplianceV2::getPendingRequirementsQuery($hiringOrg, $role)
                ->where('position_type', 'contractor')
                ->get();

            return response([
                'pending_contractor_requirements' => $requirements,
            ]);

        });
        return $response;
    }

    public function pendingEmployeeRequirements(Request $request): Response
    {
        $role = $request->user()->role;
        $hiringOrg = $role->company;

        $pendingReqTag = $this->buildTag("pending-employee-requirements", $hiringOrg->id);
        $tags = $this->buildTagsFromRequest($request, [$pendingReqTag]);
        $key = $this->buildKeyFromRequest($request);

        $response = Cache::tags($tags)->remember($key, config('cache.time'), function () use ($request, $hiringOrg, $role) {

            if (get_class($hiringOrg) != HiringOrganization::class) {
                throw new Exception("Role was not for a hiring organization. Can't obtain compliance");
            }

            $requirements = HiringOrganizationComplianceV2::getPendingRequirementsQuery($hiringOrg, $role)
                ->where('position_type', 'employee')
                ->get();

            return response([
                'pending_employee_requirements' => $requirements,
            ]);

        });
        return $response;
    }

    public function warningInternalRequirements(Request $request)
    {

        try {

            $role = $request->user()->role;
            $hiringOrg = $role->company;

            $pendingReqTag = $this->buildTag("pending-warning-requirements", $hiringOrg->id);
            $tags = $this->buildTagsFromRequest($request, [$pendingReqTag]);
            $key = $this->buildKeyFromRequest($request);

            $response = Cache::tags($tags)->remember($key, config('cache.time'), function () use ($request, $hiringOrg, $role) {

                if (get_class($hiringOrg) != HiringOrganization::class) {
                    throw new Exception("Role was not for a hiring organization. Can't obtain compliance");
                }

                $pendingRequirementsQuery = HiringOrganizationComplianceV2::getPendingRequirementsQuery($hiringOrg, $role);
                $internalRequirementsQuery = DB::table(DB::raw("({$pendingRequirementsQuery->toSql()}) as pending_requirements"))
                    ->where("pending_requirements.type", "LIKE", DB::raw("'internal%'"))
                    ->where(function ($query) {
                        $query->where("pending_requirements.position_type", DB::raw("'contractor'"));
                        $query->orWhere("pending_requirements.position_type", DB::raw("'resource'"));
                    });

                $requirements = $internalRequirementsQuery->get();

                return response([
                    'warning_requirements' => $requirements,
                ]);

            });
            return $response;
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], 400);
        }
    }


    public function warningEmployeeInternalRequirements(Request $request)
    {
        try {

            $role = $request->user()->role;
            $hiringOrg = $role->company;

            $pendingReqTag = $this->buildTag("pending-warning-requirements", $hiringOrg->id);
            $tags = $this->buildTagsFromRequest($request, [$pendingReqTag]);
            $key = $this->buildKeyFromRequest($request);

            $response = Cache::tags($tags)->remember($key, config('cache.time'), function () use ($request, $hiringOrg, $role) {

                if (get_class($hiringOrg) != HiringOrganization::class) {
                    throw new Exception("Role was not for a hiring organization. Can't obtain compliance");
                }

                $pendingRequirementsQuery = HiringOrganizationComplianceV2::getPendingRequirementsQuery($hiringOrg, $role);
                $internalRequirementsQuery = DB::table(DB::raw("({$pendingRequirementsQuery->toSql()}) as pending_requirements"))
                    ->where("pending_requirements.type", "LIKE", DB::raw("'internal%'"))
                    ->where("pending_requirements.position_type", DB::raw("'employee'"));

                $requirements = $internalRequirementsQuery->get();

                return response([
                    'warning_requirements' => $requirements,
                ]);

            });
            return $response;
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function approveRequirement(Request $request, RequirementHistory $requirementHistory): Response
    {

        $this->validate($request, [
            'notes' => 'max:65535',
        ]);

        $role = $request->user()->role;
        $hiringOrg = $role->company;

        if ($requirementHistory->requirement->hiring_organization_id !== $request->user()->role->entity_id) {
            return response([
                'message' => 'Not Authorized',
            ]);
        }

        RequirementHistoryReview::updateOrCreate(
            [
                'requirement_history_id' => $requirementHistory->id,
            ],
            [
                'status' => 'approved',
                'notes' => $request->get('notes', ''),
                'status_at' => now(),
                'approver_id' => $request->user()->current_role_id,
            ]);

        Cache::tags([
            $this->getCompanyCacheTag($role),
            $this->buildTag("pending-corporate-requirements", $hiringOrg->id),
            $this->buildTag("pending-employee-requirements", $hiringOrg->id)
        ])->flush();

        return response([
            'status' => 'approved',
        ]);
    }

    public function declineRequirement(Request $request, RequirementHistory $requirementHistory): Response
    {

        try {

            $this->validate($request, [
                'notes' => 'required|min:3|max:65535',
            ]);

            $role = $request->user()->role;
            $hiringOrg = $role->company;

            if ($requirementHistory->requirement->hiring_organization_id !== $request->user()->role->entity_id) {
                return response([
                    'message' => 'Not Authorized',
                ]);
            }

            RequirementHistoryReview::updateOrCreate(
                [
                    'requirement_history_id' => $requirementHistory->id,
                ],
                [
                    'status' => 'declined',
                    'notes' => $request->get('notes'),
                    'status_at' => now(),
                    'approver_id' => $request->user()->current_role_id,
                ]);

            // Sending requirement declined notification
            try{
                $requirementRole = $requirementHistory->role;
                if(!isset($requirementRole)){
                    throw new Exception("Association with the contracting company was not found. User may no longer be associated with the company.");
                }
                $requirementUser = $requirementRole->user;
                if(!isset($requirementUser)){
                    throw new Exception("User was not found");
                }

                $contractorId = $requirementHistory->contractor_id;
                $contractor = Contractor::find($contractorId);
                if (!isset($contractor)) {
                    throw new Exception("Contractor could not be found");
                }
                $requirementUser->notify(new Declined($contractor, $requirementHistory->requirement,
                    $request->get('notes')));
            } catch (Exception $e){
                $message = "Could not notify user.";
                return response([
                    'message' => "$message " . $e->getMessage()
                ], 400);
            }

            Cache::tags([
                $this->getCompanyCacheTag($role),
                $this->buildTag("pending-corporate-requirements", $hiringOrg->id),
                $this->buildTag("pending-employee-requirements", $hiringOrg->id)
            ])->flush();

            return response([
                'status' => 'rejected',
            ]);

        } catch (Exception $e) {
            Log::channel('slack')->error(__METHOD__.": ".$e->getMessage(), [
                'requirement_history_id' => $requirementHistory->id
            ]);
            return response([
                'message' => "There was an error declining the requirement. Please refresh the page and try again."
            ], 400);
        }
    }

    public function pendingContractorExclusions(Request $request): Response
    {
        return response([
            'pending_contractor_exclusions' => HiringOrganizationCompliance::getContractorPendingExclusions($request->user()->role)->values()->all(),
        ]);
    }

    public function pendingEmployeeExclusions(Request $request): Response
    {
        return response([
            'pending_employee_exclusions' => HiringOrganizationCompliance::getEmployeePendingExclusions($request->user()->role)->values()->all(),
        ]);
    }

    public function approveExclusion(Request $request, ExclusionRequest $exclusionRequest): Response
    {

        $this->validate($request, [
            'notes' => 'max:120',
        ]);

        if ($exclusionRequest->requirement->hiring_organization_id !== $request->user()->role->entity_id) {
            return response([
                'message' => 'Not Authorized',
            ]);
        }

        $exclusionRequest->update([
            'status' => 'approved',
            'response_role_id' => $request->user()->current_role_id,
            'responded_at' => now(),
            'responder_note' => $request->get('notes'),
        ]);

        // Sending approved notification
        $requesterUser = Role::find($exclusionRequest->requester_role_id)->user;
        $requesterUser->notify(new ExclusionApproved(
            $exclusionRequest->requirement,
            $request->get('notes'),
            $exclusionRequest->requirement->hiring_organization_id
        ));

        $role = $request->user()->role;
        Cache::tags([$this->getCompanyCacheTag($role)])->flush();

        return response(['message' => 'approved'], 200);
    }

    public function declineExclusion(Request $request, ExclusionRequest $exclusionRequest): Response
    {
        $this->validate($request, [
            'notes' => 'required|min:3|max:120',
        ]);

        if ($exclusionRequest->requirement->hiring_organization_id !== $request->user()->role->entity_id) {
            return response([
                'message' => 'Not Authorized',
            ], 403);
        }

        $exclusionRequest->update([
            'status' => 'rejected',
            'response_role_id' => $request->user()->current_role_id,
            'responded_at' => now(),
            'responder_note' => $request->get('notes'),
        ]);

        // Sending decline notification
        $requesterUser = Role::find($exclusionRequest->requester_role_id)->user;
        $requesterUser->notify(new ExclusionDeclined($exclusionRequest->requirement, $request->get('notes'),
            $exclusionRequest->requirement->hiring_organization_id));

        $role = $request->user()->role;
        Cache::tags([$this->getCompanyCacheTag($role)])->flush();

        return response(['message' => 'Requirement Request Exclusion Declined'], 200);
    }

    public function contractorPositionCompliance(Request $request, Contractor $contractor): Response
    {
        try {

            $hiringOrg = $request->user()->role->company;

            $positionsQuery = HiringOrganizationComplianceV2::getContractorComplianceByPositionQuery($contractor, $hiringOrg);
            $positions = $positionsQuery->get();

            return response([
                'positions' => $positions,
                'contractor_name' => $contractor->name,
            ]);

        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return response([
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    public function contractorResourceCompliance(Request $request, Contractor $contractor): Response
    {

        $positions = HiringOrganizationCompliance::contractorComplianceByResource($request->user()->role,
            $contractor->id)->values()->all();
        return response([
            'positions' => $positions,
            'contractor_name' => $contractor->name,
        ]);
    }

    public function contractorPositionRequirements(Request $request, Contractor $contractor, Position $position): Response {

        $requirements_collection = collect($contractor->requirements()->where('hiring_organization_id', $request->user()->role->entity_id)->where('position_id', $position->id)->get());

        $requirements = $requirements_collection->unique('requirement_id')->values()->all();

        foreach ($requirements as $requirement){
            $requirement['matching_department'] = Requirement::find($requirement->requirement_id)->matchingDepartments($request->user()->role);
        }

        $result = [
            'contractor_name' => $contractor->name,
            'position_name' => $position->name,
            'requirements' => $requirements,
        ];

        return response($result);
    }

    public function contractorResourcePositionRequirements(Request $request, Contractor $contractor, Position $position, Resource $resource): Response {
        $resource_position_requirements = collect($contractor->resourcePositionRequirements()
            ->where('hiring_organization_id', $request->user()->role->entity_id)
            ->where('position_id', $position->id)
            ->where('resource_id', $resource->id)
            ->get())
            ->unique('requirement_id')
            ->values()
            ->all();

        foreach ($resource_position_requirements as $requirement){
            $requirement['matching_department'] = Requirement::find($requirement->requirement_id)->matchingDepartments($request->user()->role);
        }

        $result = [
            'contractor_name' => $contractor->name . ' / '. $resource->name,
            'position_name' => $resource->name,
            'requirements' => $resource_position_requirements,
        ];

        return response($result);
    }


    public function contractorEmployeeCompliance(Request $request, Contractor $contractor): Response
    {
        //list of contractor employees and their overall compliance

        $employees = HiringOrganizationCompliance::contractorEmployeeOverallCompliance($request->user()->role,
            $contractor->id)->values()->all();

        //static
        return response([
            'contractor_name' => $contractor->name,
            'employees' => $employees,
        ]);
    }

    public function contractorEmployeePositionCompliance(Request $request, Contractor $contractor, Role $role): Response
    {
        //list of the contractor employee positions and their overall compliance

        $positions = HiringOrganizationCompliance::contractorEmployeeComplianceByPosition($request->user()->role,
            $role->id)->values()->all();

        //static
        return response([
            'contractor_name' => $contractor->name,
            'employee_first_name' => $role->user->first_name,
            'employee_last_name' => $role->user->last_name,
            'positions' => $positions,
        ]);
    }

    public function contractorResourcePositionCompliance(Request $request, Contractor $contractor, Resource $resource): Response
    {
        $resourceComplianceByPosQuery = $resource->complianceByHiringOrganizationPositions()
            // ->where('contractor_id', $contractor->id)
            ->where('resource_id', $resource->id)
            ->select([
                "*",
                "position_id as id",
                "position_name as name",
                "hiring_org_compliance as compliance",
            ]);


        $resourceComplianceByPos = $resourceComplianceByPosQuery->get();

        return response(['positions' => $resourceComplianceByPos]);
    }

    public function contractorEmployeePositionRequirements(Request $request, Contractor $contractor, Role $role, Position $position): Response {
        //list of requirements  for an employee filtered by position

        $requirements = collect($role->requirements()->where('hiring_organization_id', $request->user()->role->entity_id)->where('position_id', $position->id)->get())->unique('requirement_id')->values()->all();

        $user_role = $request->user()->role;

        foreach ($requirements as $requirement){
            $requirement['matching_department'] = Requirement::find($requirement->requirement_id)->matchingDepartments($user_role);
        }

        //static
        return response([
            'contractor_name' => $contractor->name,
            'employee_first_name' => $role->user->first_name,
            'employee_last_name' => $role->user->last_name,
            'position_name' => $position->name,
            'requirements' => $requirements,

        ]);

    }

    public function getRequirementHistoryAttachment(Request $request, RequirementHistory $requirementHistory)
    {

        if ($requirementHistory->requirement->hiring_organization_id !== $request->user()->role->entity_id) {
            return response([
                'message' => 'Not Authorized',
            ]);
        }

        $attachment = null;

        if (isset($requirementHistory->file)) {
            $attachment = $requirementHistory->file->getFullPath();
        }

        if (isset($attachment)) {
            return response([
                'attachment' => $attachment,
            ]);
        } else {
            return response([
                'message' => 'File could not be found',
                'requirement_history_id' => $requirementHistory->id
            ], 404);
        }

    }

    public function upload(Request $request, Requirement $requirement): Response
    {
        try {

            $role = $request->has('role_id') ?
                Role::find($request->get('role_id')) :
                Role::where('entity_id', $request->get('contractor_id'))->where('entity_key', 'contractor')->first();

            if (!isset($role) && !is_null($role)) {
                throw new Exception("Role not found.");
            }

            if (!isset($role->entity_key) || $role->entity_key != 'contractor') {
                throw new Exception("Cannot perform this action");
            }

            if ($requirement->type !== 'upload' && $requirement->type !== 'internal_document') {
                throw new Exception("Wrong type of requirement");
            }

            if ($requirement->hiring_organization_id !== $request->user()->role->entity_id) {
                throw new Exception("Not Authorized");
            }

            $filetypes = config('filetypes.documents_string');
            $maxFileSize = config('filesystems.max_size', 10240);

            // $this->validate($request, [
            //     "attachment" => "required|file|max:$maxFileSize|mimes:$filetypes",
            //     "contractor_id" => "required_without:role_id|exists:contractors,id",
            //     "role_id" => "required_without:contractor_id|exists:roles,id",
            // ]);


            $file = $this->createFileFromRequest($request, 'attachment');

            // Determining renewal date: Now, or use the hard_deadline_date
            $completionDate = now();
            $hard_deadline_date = $requirement->hard_deadline_date;
            if ($hard_deadline_date) {
                $renewal_period = $requirement->renewal_period;
                $completionDate = Carbon::createFromFormat('Y-m-d', $hard_deadline_date)->subMonths($renewal_period)->format('Y-m-d');
            }

            //Store the file(s) and create Requirement History
            $reqHistoryFileObj = new \stdClass;
            $reqHistoryFileObj->requirement_id = $requirement->id;
            $reqHistoryFileObj->completion_date = $completionDate;
            $reqHistoryFileObj->role_id = $role->id;
            $reqHistoryFileObj->contractor_id = $role->entity_id;
            $reqHistoryFileObj->file = $file;
            $reqHistoryFileObj->resource_id = $request->resource_id;

            $history = $this->createRequirementHistoryFile($reqHistoryFileObj);

            // Clearing relative cache
            Cache::tags($this->buildTagsFromRequest($request))->flush();

            return response([
                'message' => 'ok',
                'requirement_history' => $history,
            ], 200);
        } catch (Exception $exception) {
            Log::error($exception);
            return response(['message' => $exception->getMessage()], 500);
        }
    }

    public function uploadWithDate(Request $request, Requirement $requirement): Response
    {
        try {
            if ($requirement->type !== 'upload_date') {
                return response(['error' => 'Wrong type of requirement'], 407);
            }

            if ($requirement->hiring_organization_id !== $request->user()->role->entity_id) {
                return response(['error' => 'Not Authorized', 403]);
            }

            $filetypes = config('filetypes.documents_string');
            $maxFileSize = config('filesystems.max_size', 10240);

            // $this->validate($request, [
            //     "attachment" => "required|file|max:$maxFileSize|mimes:$filetypes",
            //     "expiry-date" => "required|date",
            //     "contractor_id" => "required_without:role_id|exists:contractors,id",
            //     "role_id" => "required_without:contractor_id|exists:roles,id",
            // ]);

            $role = $request->has('role_id') ? Role::find($request->get('role_id')) : Role::where('entity_id',
                $request->get('contractor_id'))->where('entity_key', 'contractor')->first();

            $file = $this->createFileFromRequest($request, 'attachment');

            $completion_date = Carbon::createFromFormat('Y-m-d',
                $request->get('expiry-date'))->subMonths($requirement->renewal_period);
            $expired = Carbon::createFromFormat('Y-m-d', $request->get('expiry-date'))->lessThan(now());

            if ($expired) {
                return response([
                    'errors' => [
                        'certificate_expiry_date' => [
                            'Expiry date not valid', //TODO translate this
                        ],
                    ]
                ], 422); //TODO translate this
            }

            if (!isset($role->entity_key) || $role->entity_key != 'contractor') {
                throw new Exception("Role not found or cannot perform this action");
            }

            //Store the file(s) and create Requirement History

            $reqHistoryFileObj = new \stdClass;
            $reqHistoryFileObj->requirement_id = $requirement->id;
            $reqHistoryFileObj->completion_date = $completion_date;
            $reqHistoryFileObj->role_id = $role->id;
            $reqHistoryFileObj->contractor_id = $role->entity_id;
            $reqHistoryFileObj->file = $file;
            $reqHistoryFileObj->resource_id = $request->resource_id;

            $history = $this->createRequirementHistoryFile($reqHistoryFileObj);

            return response([
                'message' => 'ok',
                'requirement_history' => $history,
            ], 200);
        } catch (Exception $exception) {
            Log::error($exception);
            return response(['message' => $exception->getMessage()], 500);
        }
    }

    private function getRequestFilters(Request $request)
    {
        $filterParams = $request->only(
            "position",
            "facility",
            "department"
        );

        $filterParams = collect($filterParams)
            ->map(function ($filter) {
                // explode on null returns "", instead of null.
                // Only returning if the filter is set.
                if (isset($filter)) {
                    return explode(',', $filter);
                }
            })
            // Removing nulls
            ->filter();

        return $filterParams
            ->toArray();
    }

}
