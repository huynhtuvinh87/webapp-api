<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Role;
use App\Models\Module;
use App\Models\ModuleVisibility;
use Tests\ModuleVisibilityBaseTestClass;

class ModuleVisibilityRoleTest extends ModuleVisibilityBaseTestClass
{
    /**
     * Testing Module visibility - Hiring Org relation
     *
     * @group ModuleVisibility
     * @group DynamicForms
     * @return void
     */
    public function testModuleVisRoleRelation()
    {
        // Checking visibility given hiring org
        $this->assertNotNull(static::$role_hiringOrg->moduleVisibility);
        $this->assertGreaterThan(0, sizeof(static::$role_hiringOrg->moduleVisibility));
    }

    /**
     * Testing Hiring Org method: isModuleVisible
     *
     * @group ModuleVisibility
     * @group DynamicForms
     * @return void
     */
    public function testRoleVisMethodById()
    {
        // Checking visibility of specific module
        $roleVis = static::$role_hiringOrg->isModuleVisible(static::$module->id);
        $this->assertNotNull($roleVis);
        $this->assertEquals($roleVis, static::$moduleVisRole['visible']);
    }

    /**
     * Testing Hiring Org method: isModuleVisible using module name
     *
     * @group ModuleVisibility
     * @group DynamicForms
     * @return void
     */
    public function testRoleVisMethodByName()
    {
        // Getting visibility by name
        $visByName = static::$role_hiringOrg->isModuleVisible(Module::where('name', static::$module->name)->first()->id);

        $this->assertNotNull($visByName);
        $this->assertEquals($visByName, static::$moduleVisRole['visible']);
    }
}
