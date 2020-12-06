<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Error;
use Illuminate\Http\Request;

class ModuleController extends Controller
{

    // =============== Request Handlers ========== //
    /**
     * Returns a response of all visibilities assigned to the current role
     *
     * @param Request $request
     * @return void
     */
    public function getRoleVisibilities(Request $request)
    {
        $responseObj = array_merge($this->roleVisibilities($request->user()->role));

        return response($responseObj);
    }

    /**
     * Returns array of all visibilities for current user
     *
     * @param Request $request
     * @return void
     */
    public function getAllVisibilities(Request $request)
    {
        $responseObj = array_merge(
            $this->roleVisibilities($request->user()->role),
            $this->hiringOrgVisibilities($request->user()->role->company),
            $this->contractorVisibilities($request->user()->role->company),
        );

        return response($responseObj);
    }

    /**
     * Returns visibility status based on current user and module
     * Only returns 1 / 0 for each level
     *
     * @param Request $request
     * @param Module $module
     * @return void
     */
    public function getModuleVisibilities(Request $request, Module $module)
    {
        if (!isset($module)) {
            throw new Error("Module was not defined");
        }
        if (!isset($module->id)) {
            throw new Error("Module has no ID");
        }

        $responseObj = array_merge(
            $this->moduleVisibilityDefault($module),
            $this->moduleVisibilityByRole($module, $request->user()->role),
            $this->moduleVisibilityByHiringOrg($module, $request->user()->role->company),
            $this->moduleVisibilityByContractor($module, $request->user()->role->company),
        );

        return response($responseObj);
    }

    /**
     * Get all modules
     *
     * @param Request $request
     * @return void
     */
    public function getModules(Request $request)
    {
        return response([
            'modules' => Module::get()
        ]);
    }

    // =============== Helpers =============== //

    protected function moduleVisibilityDefault($module)
    {
        if (!isset($module)) {
            throw new Error("Module was undefined");
        }

        $defaultVis = $module->visible;

        return [
            'default_visibility' => $defaultVis,
        ];
    }

    protected function moduleVisibilityByRole($module, $role)
    {
        if (!isset($module)) {
            throw new Error("Module was undefined");
        }
        $visibilities = $role->isModuleVisible($module->id);
        return [
            'role_visibilities' => $visibilities,
        ];
    }

    protected function moduleVisibilityByHiringOrg($module, $hiringOrg)
    {
        if (!isset($module)) {
            throw new Error("Module was undefined");
        }

        if (class_basename($hiringOrg) == "HiringOrganization") {
            $visibilities = $hiringOrg->isModuleVisible($module->id);
        } else {
            $visibilities = null;
        }

        return [
            'hiring_organization_visibilities' => $visibilities,
        ];
    }

    protected function moduleVisibilityByContractor($module, $contractor)
    {
        if (!isset($module)) {
            throw new Error("Module was undefined");
        }

        if (class_basename($contractor) == "Contractor") {
            $visibilities = $contractor->isModuleVisible($module->id);
        } else {
            $visibilities = null;
        }
        return [
            'contractor_visibilities' => $visibilities,
        ];
    }

    protected function roleVisibilities($role)
    {
        $visibilities = $role->moduleVisibility;
        return [
            'role_visibilities' => $visibilities,
        ];
    }

    protected function hiringOrgVisibilities($hiringOrg)
    {
        if (class_basename($hiringOrg) == "HiringOrganization") {
            $visibilities = $hiringOrg->moduleVisibility;
        } else {
            $visibilities = null;
        }
        return [
            'hiring_organization_visibilities' => $visibilities,
        ];
    }

    protected function contractorVisibilities($contractor)
    {
        if (class_basename($contractor) == "Contractor") {
            $visibilities = $contractor->moduleVisibility;
        } else {
            $visibilities = null;
        }

        return [
            'contractor_visibilities' => $visibilities,
        ];
    }
}
