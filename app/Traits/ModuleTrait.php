<?php

namespace App\Traits;

trait ModuleTrait
{
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

    /**
     * Returns all the visibilities for the module by entity key
     *
     * @param [type] $module
     * @param [type] $entityKey
     * @return void
     */
    protected function allModuleVisibilitiesByEntity($module, $entityType)
    {
        $visibilities = $module
            ->moduleVisibility
            ->where('entity_type', $entityType);

        return $visibilities;
    }

    protected function allModuleVisibilities($module)
    {
        $visibilities = [
            'role_visibilities' => $this->allModuleVisibilitiesByEntity($module, 'role'),
            'hiring_organization_visibilities' => $this->allModuleVisibilitiesByEntity($module, 'hiring_organization'),
            'contractor_visibilities' => $this->allModuleVisibilitiesByEntity($module, 'contractor'),
        ];

        return $visibilities;
    }

}
