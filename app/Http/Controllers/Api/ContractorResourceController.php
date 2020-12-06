<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Lib\Services\HiringOrganizationCompliance;
use App\Models\Facility;
use App\Models\Position;
use App\Models\Resource;
use App\Models\Role;
use App\Models\User;
use App\Traits\AutoAssignTrait;
use App\Traits\CacheTrait;
use App\ViewModels\ViewContractorResourceComplianceByHiringOrg;
use App\ViewModels\ViewContractorResourceOverallCompliance;
use App\ViewModels\ViewContractorResourcePositionRequirements;
use DB;
use Exception;
use Illuminate\Http\Request;
use Log;

/**
 * Controller for Contractor admins to manage contractor employees
 *
 * Class ContractorEmployeeController
 * @package App\Http\Controllers\Api
 */
class ContractorResourceController extends Controller
{
    use CacheTrait;
    use AutoAssignTrait;

    public function index(Request $request)
    {
        $contractor_id = $request->user()->role->company->id;

        $resourcesQuery = ViewContractorResourceComplianceByHiringOrg
            ::where('contractor_id', $contractor_id);

        if ($request->has('hiring_organization_id')) {
            $resourcesQuery->where('hiring_organization_id', '=', $request->get('hiring_organization_id'));
        }

        $resources = $resourcesQuery->get();


        return response($resources);
    }

    public function store(Request $request)
    {
        $resource = Resource::create(["name" => $request->name, "contractor_id" => $request->user()->role->entity_id]);
        return response(["resource" => $resource]);
    }

    /**
     * Assign a new position to a resource
     * @param Request $request
     * @param Resource $resource
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function assignPosition(Request $request, Resource $resource)
    {

        $this->validate($request, [
            "position_id" => "required|exists:positions,id"
        ]);

        $position = Position::find($request->get('position_id'));
        if (!$position || is_null($position)) {
            throw new Exception("Position not found, position not assigned");
        }

        $current_positions = $resource->positions()->pluck('position_id')->toArray();

        if (in_array($position->id, $current_positions)) {
            return response(["message" => "Resource already has the position"], 200);
        }

        $resource->positions()->attach($position->id);

        return response(["message" => "Position Added to Resource"], 200);

    }

    public function unassignPosition(Request $request, Resource $resource)
    {

        $this->validate($request, [
            "position_id" => "required|exists:positions,id"
        ]);

        $position = Position::find($request->get('position_id'));
        if (!$position || is_null($position)) {
            throw new Exception("Position not found, position not assigned");
        }

        $resource->positions()->detach($position->id);

        return response(["message" => "Resource Position Removed"], 200);

    }

    /**
     * Get users' positions
     * @param Resource $resource
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function positions(Resource $resource)
    {
        return response($resource->positions);
    }

    /**
     * @param Resource $resource
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function facilities(Resource $resource)
    {
        return response($resource->facilities);
    }

    public function assignFacility(Request $request, Resource $resource)
    {
        // Validating facility_id exists
        $this->validate($request, [
            "facility_id" => "required|exists:facilities,id"
        ]);

        $facility = Facility::find($request->get('facility_id'));
        if (!$facility || is_null($facility)) {
            throw new Exception("Position not found, position not assigned");
        }

        $current_facilities = $resource->facilities()->pluck('facility_id')->toArray();

        if (in_array($facility->id, $current_facilities)) {
            return response(["message" => "Resource already has the facility"], 200);
        }

        $resource->facilities()->attach($facility->id);

        $this->autoAssignByFacility($facility, null, true);

        return response(["message" => "Facility Added to Resource"], 200);
    }

    public function unassignFacility(Request $request, Resource $resource)
    {
        $this->validate($request, [
            "facility_id" => "required|exists:facilities,id"
        ]);

        $facility = Facility::find($request->get('facility_id'));
        if (!$facility || is_null($facility)) {
            throw new Exception("Position not found, position not assigned");
        }

        $resource->facilities()->detach($facility->id);

        return response(["message" => "Facility Removed"], 200);
    }

    public function role(Resource $resource)
    {
        $roles = $resource->roles;
        foreach ($roles as $role){
            $user = User::find($role->user_id);
            $role->user = ($user->last_name) ? $user->first_name . " " . $user->last_name : $user->first_name;
        }
        return response($roles);
    }

    public function assignRole(Request $request, Resource $resource)
    {
        // Validating role_id exists
        $this->validate($request, [
            "role_id" => "required|exists:roles,id"
        ]);

        $role = Role::find($request->get('role_id'));
        if (!$role || is_null($role)) {
            throw new Exception("Employee/Resource attached");
        }

        $resource->roles()->sync([$role->id]);

        return response(["message" => "Employee added to the resource"], 200);
    }

    public function unassignRole(Request $request, Resource $resource)
    {
        // Validating role_id exists
        $this->validate($request, [
            "role_id" => "required|exists:roles,id"
        ]);

        $role = Role::find($request->get('role_id'));
        if (!$role || is_null($role)) {
            throw new Exception("Role not found, resource not assigned");
        }

        $resource->roles()->detach($role->id);

        return response(["message" => "Employee/Resource removed"], 200);
    }

    /**
     * Destroy resource
     * @param Request $request
     * @param Resource $resource
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws Exception
     */
    public function destroy(Request $request, Resource $resource)
    {

        $position_ids = $resource->positions()->pluck('positions.id');
        $resource->positions()->detach($position_ids);

        $facility_ids = $resource->facilities()->pluck('facilities.id');
        $resource->facilities()->detach($facility_ids);

        $employee_ids = $resource->roles()
            ->where('entity_key', 'contractor')
            ->where('entity_id', $request->user()->role->company->id)
            ->pluck('roles.id');
        $resource->roles()->detach($employee_ids);

        $resource->delete();

        return response(["message" => "Resource Deleted"], 200);

    }

    public function compliance(Request $request)
    {
        $overallResourceCompliance = ViewContractorResourceOverallCompliance::where('resource_id', $request->get('resource_id'))->first();

        return response($overallResourceCompliance);
    }

    public function companyCompliance(Request $request)
    {
        $resource = $this->userContext($request);

        if ($resource === false) {
            return response('Not Authorized', 403);
        }

        $resourceCompliance = $resource->complianceByHiringOrganization()
            ->where('requirement_count', '>', 0)
            ->get();
        return response($resourceCompliance);
    }

    /**
     * If resource_id is provided, ensure user is admin and the resource is of the same organization,
     * all admin to implement routes
     * @param $request
     * @return User|bool
     * @throws Exception
     */
    private function userContext($request)
    {

        if ($request->query('resource_id') && is_numeric($request->query('resource_id'))) {
            $company = $request->user()->role->company;

            $resource = Resource::find($request->query('resource_id'));

            if ($company->id != $resource->contractor_id) {
                return false;
            }

        }

        if (!$resource || is_null($resource)) {
            throw new Exception("Resource not found");
        }

        return $resource;

    }

    public function companyRequirements(Request $request, $id)
    {

        $resource = Resource::find($request['resource_id']);
        $resourceRequirements = $resource->requirements()->where('hiring_organization_id', $id)
        ->get()
        ->unique('requirement_id')
        ->values()
        ->all();

            return response([
            "compliance" => $resource->complianceByHiringOrganizationPositions()->where('hiring_organization_id', $id)->get(),
            "requirements" => $resourceRequirements
        ]);

    }

    public function getPastDueRequirements(Request $request, Resource $resource)
    {

        $pastDueRequirementsQuery = ViewContractorResourcePositionRequirements
            ::where('resource_id', DB::raw("$resource->id"))
            ->where('requirement_status', DB::raw("'past_due'"))
            ->where('requirement_type', '!=', 'internal_document')
            ->whereNotNull('requirement_id')
            ->select([
                DB::raw("DISTINCT requirement_id"),
                DB::raw("MAX(position_id) as position_id"),
            ])
            ->groupBy("requirement_id");

        $pastDueRequirements = $pastDueRequirementsQuery->get();

        $query = ViewContractorResourcePositionRequirements
            ::joinSub($pastDueRequirementsQuery, "resource_requirement", function ($join) {
                $join->on("resource_requirement.requirement_id", "view_contractor_resource_position_requirements.requirement_id");
                $join->on("resource_requirement.position_id", "view_contractor_resource_position_requirements.position_id");
            })
            ->where('resource_id', DB::raw("$resource->id"));

        $results = $query->get();

        return response(['past_due' => $results], 200);
    }

}
