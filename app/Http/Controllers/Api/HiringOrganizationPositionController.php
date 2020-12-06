<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Notifications\SendNewRequirementNotification;
use App\Models\Contractor;
use App\Models\Facility;
use App\Models\Position;
use App\Models\Requirement;
use App\Models\Role;
use App\Models\User;
use App\Traits\AutoAssignTrait;
use App\Traits\CacheTrait;
use App\Traits\NotificationTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HiringOrganizationPositionController extends Controller
{

	use AutoAssignTrait;
	use NotificationTrait;
	use CacheTrait;

    /**
     * @query string $type 'employee'|'contractor' | default null
     * @query bool $requirement_count | default False
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $type = null;
        $requirement_count = false;

        if ($request->get('type') && in_array($request->get('type'), ['employee', 'contractor', 'resource'], true)){
            $type = $request->get('type');
        }

        if ($request->get('requirement_count')){
            $requirement_count = true;
        }

        $positions = $request->user()->role->company->positions();

        if ($type){
            $positions->where('position_type', $type);
        }

        if ($requirement_count){
            $positions->withCount('requirements');
        }

        return response(['positions' => $positions->get()]);

    }

    public function show(Request $request, Position $position)
    {

        if (!$this->belongsToOrg($request->user(), $position)) {
            return response(['message' => 'Not authorized'], 403);
        }

        $position->load(['facilities', 'requirements']);
        foreach($position->requirements as $requirement){
            // Calling loadContent on each requirement to load in the localizedContent element
            $requirement->loadContent();
        }

        return response(['position' => $position]);

    }

    public function store(Request $request){
        $this->validate($request, [
            'name' => 'required|string',
            'position_type' => 'required|in:employee,contractor,resource',
            'auto_assign' => 'numeric|in:0,1',
            'is_active' => 'numeric|in:0,1'
        ]);

        if (Position::where('name', $request->get('name'))->where('hiring_organization_id',
            $request->user()->role->entity_id)->where('position_type', $request->get('position_type'))->exists()) {
            return response([
                'errors' => [
                    'name' => [
                        __('validation.unique', ['attribute' => 'name'])
                    ]
                ]
            ], 418);
        }

        $position = $request->user()->role->company->positions()->create($request->all());

        $position->position_type = $request->get('position_type');

        $position->save();

		// Calls auto assign for the position
		if($position['auto_assign'] == true){
			$this->autoAssignByPosition($position, true);
		}

        Cache::tags($this->buildTagsFromRequest($request))->flush();

        return response(['position' => $position]);
    }

    public function update(Request $request, Position $position)
    {

        if (!$this->belongsToOrg($request->user(), $position)) {
            return response(['message' => 'Not authorized'], 403);
        }

        $this->validate($request, [
            'name' => 'string',
            'auto_assign' => 'numeric|in:0,1',
            'is_active' => 'numeric|in:0,1'
        ]);

        if (Position::where('name', $request->get('name'))->where('hiring_organization_id',
            $request->user()->role->entity_id)->where('id', '!=', $position->id)->exists()) {
            return response([
                'errors' => [
                    'name' => [
                        __('validation.unique', ['attribute' => 'name'])
                    ]
                ]
            ], 418);
        }

		$position->update($request->all());

		// Calls auto assign for the position
		if($position['auto_assign'] == true){
			$this->autoAssignByPosition($position, true);
		}

        Cache::tags($this->buildTagsFromRequest($request))->flush();

        return response(['position' => $position]);

    }

    /**
     * DEPRECATED - can make inactive
     * @param Request $request
     * @param Position $position
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Request $request, Position $position){
        if (!$this->belongsToOrg($request->user(), $position)) {
            return response(['message' => 'Not authorized'], 403);
        }

        $position->delete();

        return response('ok');

    }

    public function addFacilities(Request $request, Position $position){
        $this->validate($request, [
            'facility_ids' => 'array|min:1',
            'facility_ids.*' => 'numeric'
        ]);

        if ($position->hiring_organization_id !== $request->user()->role->entity_id){
            return response(['message' => 'Not authorized'], 403);
        }

        foreach ($request->get('facility_ids') as $facility_id){

            $facility = Facility::find($facility_id);
            if ($facility->hiring_organization_id === $request->user()->role->entity_id){
                $facility->positions()->sync($position->id, false);
            }

        }

        return response(['message' => 'ok']);

		// Calls auto assign for the position
		$this->autoAssignByFacility($facility, null, true);

    }

    public function removeFacilities(Request $request, Position $position){
        $this->validate($request, [
            'facility_ids' => 'array|min:1',
            'facility_ids.*' => 'numeric'
        ]);

        if ($position->hiring_organization_id !== $request->user()->role->entity_id){
            return response(['message' => 'Not authorized'], 403);
        }

        $position->facilities()->detach($request->get('facility_ids'));

        return response(['message' => 'ok']);

    }

    public function removeRequirements(Request $request, Position $position){
        $this->validate($request, [
            'requirement_ids' => 'array|min:1',
            'requirement_ids.*' => 'numeric'
        ]);

        if (!$this->belongsToOrg($request->user(), $position)){
            return response(['message' => 'Not authorized'], 403);
        }

        $position->requirements()->detach($request->get('requirement_ids'));

        return response(['message' => 'ok']);

    }

    public function addRequirements(Request $request, Position $position){
        try {
            $this->validate($request, [
                'requirement_ids' => 'array|min:1',
                'requirement_ids.*' => 'numeric'
            ]);

            if (!$this->belongsToOrg($request->user(), $position)) {
                return response(['message' => 'Not authorized'], 403);
            }

            $isPositionActive = $position['is_active'];
            $isEmployeePosition = $position['position_type'] === 'employee';

            if(!$isPositionActive) {
                return response(['message' => 'Position inactive, no actions taken.'], 400);
            }

            foreach ($request->get('requirement_ids') as $requirement_id) {

                $requirement = Requirement::find($requirement_id);
                if ($requirement->hiring_organization_id === $request->user()->role->entity_id) {
                    $requirement->positions()->attach($position->id);
                }

            }

            // Send Notification to Contractor Owner or Employee
            if ($isEmployeePosition) {

                $roles = DB::table('position_role')
                    ->where('position_id', $position->id)
                    ->whereNull('deleted_at')
                    ->distinct()
                    ->pluck('role_id');

                foreach ($roles as $role_id) {
                    $role = Role::find($role_id);
                    if (!is_null($role) && $this->checkContractorSubscriptionStatus($role->company)) {
                        SendNewRequirementNotification::dispatch($role->user, $position);
                    }
                }
            } else {

                $contractors = DB::table('contractor_position')
                    ->where('position_id', $position->id)
                    ->whereNull('deleted_at')
                    ->distinct()
                    ->pluck('contractor_id');

                foreach ($contractors as $contractor_id) {
                    $contractor = Contractor::find($contractor_id);
                    if ($this->checkContractorSubscriptionStatus($contractor)) {
                        SendNewRequirementNotification::dispatch($contractor->owner->user, $position);
                    }
                }
            }

            // Calls auto assign for the position
            $this->autoAssignByPosition($position, TRUE);

            return response(['message' => 'Requirement added successfully.'], 201);

        } catch (Exception $exception){
            return response(['message' => $exception->getMessage()], 500);
        }

    }

    private function belongsToOrg($user, $position)
    {
        return $position->hiring_organization_id === $user->role->entity_id;
    }
}
