<?php

namespace App\Jobs;

use App\Models\Contractor;
use App\Models\Facility;
use App\Models\HiringOrganization;
use App\Models\Position;
use App\Models\Resource;
use App\Models\Role;
use App\Notifications\Requirement\NewRequirement;
use DB;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class AssignAutoAssignPositions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $contractor;
    private $facility;
    private $position;
    private $role;
    private $resource;

    /**
     * AssignAutoAssignPositions constructor.
     * @param Contractor $contractor
     * @param Facility|null $facility
     * @param Position|null $position
     * @param Role|null $role
     * @param Resource|null $resource
     */
    public function __construct(
        Contractor $contractor = null,
        Facility $facility = null,
        Position $position = null,
        Role $role = null,
        Resource $resource = null
    ) {
        $this->contractor = $contractor;
        $this->facility = $facility;
        $this->position = $position;
        $this->role = $role;
        $this->resource = $resource;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // If not position was specified, run updater for all positions
        if (is_null($this->position)) {
            Log::debug('isnull');
            $positions = $this->facility->positions()
                ->where('positions.auto_assign', 1)
                ->where('positions.is_active', 1)
                ->pluck('positions.id')->toArray();

            if (is_array($positions) && count($positions) > 0) {

                foreach ($positions as $key => $positionId) {
                    $pos = Position::where('id', $positionId)
                        ->get()
                        ->first();

                    try {
                        $this->handleAssignPositionToEntity($pos, $this->contractor, $this->role, $this->resource);
                    } catch (Exception $e) {
                        Log::error($e->getMessage());
                    }
                }
            }

        } else {
            try {
                $this->handleAssignPositionToEntity($this->position, $this->contractor, $this->role, $this->resource);
            } catch (Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }

    public function handleAssignPositionToEntity($position, $contractor = null, $role = null, $resource = null)
    {
        if (!isset($position)) {
            throw new Exception("Position was not set");
        }

        $assignToContractor = $position['position_type'] == 'contractor' && !is_null($contractor);
        $assignToEmployee = $position['position_type'] == 'employee' && !is_null($role);
        $assignToResource = $position['position_type'] == 'resource' && !is_null($resource);

        if ($assignToContractor) {
            // Assigning contractor position to contractor if set
            $this->syncContractorPosition($contractor, $position);
        } else if ($assignToEmployee) {
            // Assigning employee position if role is set
            $this->syncEmployeePosition($role, $position);
        } else if ($assignToResource) {
            // Assigning resource position if resource is set
            $this->syncResourcePosition($resource, $position);
        } else {
            Log::debug("Failed to sync corporate / employee position", [
                'Position Type' => $position['position_type'],
                "is_null(contractor)" => is_null($contractor),
                "is_null(role)" => is_null($role),
                "isset(contractor)" => isset($contractor),
                "isset(role)" => isset($role),
                "assignToContractor" => $assignToContractor,
                "assignToEmployee" => $assignToEmployee,

            ]);
            throw new Exception("Corporate / Employee position mismatch with contractor / role.");
        }
    }

    public function syncContractorPosition($contractor, $position)
    {
        // Error handling
        if (is_null($contractor)) {
            throw new Exception("Can't assign position - contractor was null");
        }
        if (is_null($position)) {
            throw new Exception("Can't assign position - position was null");
        }
        if ($position['position_type'] != 'contractor') {
            throw new Exception("Can't assing position - position type was not a contractor position. Was " . $position['position_type']);
        }

        // Removing position
        // $this->removeDuplicatedPositions($position, $contractor, null);

        // Assigning position
        $contractor_hiring_orgs = $contractor->hiringOrganizations()->pluck('hiring_organizations.id')->toArray();

        $is_already_assigned = DB::table('contractor_position as cp')
            ->where('cp.position_id', $position->id)
            ->where('cp.contractor_id', $contractor->id)
            ->first();

        if (!$is_already_assigned) {
            if (in_array($position->hiring_organization_id, $contractor_hiring_orgs)) {
                Log::debug("position $position->id is NOT assigned to $contractor->id, attaching it now");
                $contractor->positions()->attach($position);
            }
        }
    }

    public function syncEmployeePosition($employeeRole, $position)
    {
        // Error handling
        if (is_null($employeeRole)) {
            throw new Exception("Can't assign position - Employee role was null");
        }
        if (is_null($position)) {
            throw new Exception("Can't assign position - position was null");
        }
        if ($position['position_type'] != 'employee') {
            throw new Exception("Can't assign position - position type was not a employee position. Was " . $position['position_type']);
        }

        // Removing position
        // $this->removeDuplicatedPositions($position, null, $employeeRole);

        // Assigning position
        $is_role_already_assigned = DB::table('position_role')
            ->where('position_id', $position->id)
            ->where('role_id', $employeeRole->id)
            ->first();

        if (!$is_role_already_assigned) {
            Log::debug("position $position->id is NOT assigned to role $employeeRole->id, attaching it now");
            $employeeRole->positions()->attach($position);
        }

    }

    public function syncResourcePosition($resource, $position)
    {
        Log::info("Auto Assigning Positions RESOURCES");
        // Error handling
        if (is_null($resource)) {
            throw new Exception("Can't assign position - Resource was null");
        }
        if (is_null($position)) {
            throw new Exception("Can't assign position - position was null");
        }
        if ($position['position_type'] != 'resource') {
            throw new Exception("Can't assign position - position type was not a resource position. Was " . $position['position_type']);
        }

        // Assigning position
        $is_already_assigned = DB::table('resource_position')
            ->where('position_id', $position->id)
            ->where('resource_id', $resource->id)
            ->first();

        if (!$is_already_assigned) {
            Log::debug("position $position->id is NOT assigned to role $resource->id, attaching it now");
            $resource->positions()->attach($position);
        }

    }

    /**
     * Prevent duplication of position on pivot table
     */
    private function removeDuplicatedPositions($position, $contractor = null, $employeeRole = null)
    {
        if ($contractor) {
            $do_not_delete_this_position = DB::table('contractor_position')
                ->where('position_id', $position->id)
                ->where('contractor_id', $contractor->id)
                ->first();

            Log::debug("not removing $do_not_delete_this_position->id");

            if ($do_not_delete_this_position != null) {
                DB::table('contractor_position')
                    ->where('position_id', $position->id)
                    ->where('contractor_id', $contractor->id)
                    ->where('id', '!=', $do_not_delete_this_position->id)
                    ->delete();
            }
        } else {
            $do_not_delete_this_position = $employeeRole->positions()->where('position_id', $position->id)->first();

            if ($do_not_delete_this_position != null) {
                DB::table('position_role')
                    ->where('position_id', $position->id)
                    ->where('role_id', $employeeRole->id)
                    ->where('id', '!=', $do_not_delete_this_position->id)
                    ->delete();
            }
        }
    }
}
