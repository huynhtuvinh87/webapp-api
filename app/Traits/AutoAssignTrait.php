<?php

namespace App\Traits;

use App\Jobs\AssignAutoAssignPositions;
use App\Models\Facility;
use App\Models\Position;
use App\Notifications\Requirement\NewRequirement;
use Log;

trait AutoAssignTrait
{
	public function autoAssignByEmployee($role, $facility = null, $position = null, $forceNow = false){
        if ($forceNow) {
            AssignAutoAssignPositions::dispatchNow(null, $facility, $position, $role);
        } else {
            AssignAutoAssignPositions::dispatch(null, $facility, $position, $role);
        }
	}

    public function autoAssignByContractor($contractor, $facility, $position = null, $forceNow = false)
    {
        if ($forceNow) {
            AssignAutoAssignPositions::dispatchNow($contractor, $facility, $position);
        } else {
            AssignAutoAssignPositions::dispatch($contractor, $facility, $position);
        }
    }

    public function autoAssignByResource($resource, $facility, $position = null, $forceNow = false)
    {
        if ($forceNow) {
            AssignAutoAssignPositions::dispatchNow(null, $facility, $position, null, $resource);
        } else {
            AssignAutoAssignPositions::dispatch(null, $facility, $position, null, $resource);
        }
    }

    /**
     * Calls auto assign for all contractors at facility
     *
     * @param Facility $facility
     * @param Position|null $position
     * @param boolean $forceNow
     * @return void
     */
    public function autoAssignByFacility(Facility $facility, Position $position = null, $forceNow = false)
    {

        // Get contractors associated with facility
        $contractors = $facility->contractors;

        // For each contractor, run auto assign
        foreach ($contractors as $contractor) {
			$this->autoAssignByContractor($contractor, $facility, $position, $forceNow);

			if(!is_null($position)){
				$roles = $contractor->roles()->get();
				foreach($roles as $role){
					$this->autoAssignByEmployee($role, $facility, $position, $forceNow);
				}
			}
		}

        $resources = $facility->resources;
        foreach($resources as $resource){
            $this->autoAssignByResource($resource, $facility, $position, $forceNow);
        }

    }

    /**
     * Calls auto assign for all contractors with position
     *
     * @param [type] $position
     * @param boolean $forceNow
     * @return void
     */
    public function autoAssignByPosition(Position $position, $forceNow = false)
    {
        // Get facilities associated with position
		$facilities = $position->facilities;

        // Call autoAssignByFacility on each facility
        foreach ($facilities as $facility) {
            if ($position['auto_assign'] == true) {
				$this->autoAssignByFacility($facility, $position, $forceNow);
            }
        }
    }
}
