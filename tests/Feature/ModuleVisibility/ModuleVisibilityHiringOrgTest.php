<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\HiringOrganization;
use App\Models\Module;
use App\Models\ModuleVisibility;
use Tests\ModuleVisibilityBaseTestClass;

class ModuleVisibilityHiringOrgTest extends ModuleVisibilityBaseTestClass
{
    /**
     * Testing Module visibility - Hiring Org relation
     *
     * @group ModuleVisibility
     * @group DynamicForms
     * @return void
     */
    public function testModuleVisHiringOrgRelation()
    {
        // Checking visibility given hiring org
        $this->assertNotNull(static::$hiringOrg->moduleVisibility);
        $this->assertGreaterThan(0, sizeof(static::$hiringOrg->moduleVisibility));
    }

    /**
     * Testing Hiring Org method: isModuleVisible
     *
     * @group ModuleVisibility
     * @group DynamicForms
     * @return void
     */
    public function testHiringOrgVisMethodById()
    {
        // Checking visibility of specific module
        $hiringOrgVis = static::$hiringOrg->isModuleVisible(static::$module->id);
        $this->assertNotNull($hiringOrgVis);
        $this->assertEquals($hiringOrgVis, static::$moduleVis['visible']);
    }

    /**
     * Testing Hiring Org method: isModuleVisible using module name
     *
     * @group ModuleVisibility
     * @group DynamicForms
     * @return void
     */
    public function testHiringOrgVisMethodByName()
    {
        // Getting visibility by name
        $visByName = static::$hiringOrg->isModuleVisible(Module::where('name', static::$module->name)->first()->id);

        $this->assertNotNull($visByName);
        $this->assertEquals($visByName, static::$moduleVis['visible']);
    }
}
