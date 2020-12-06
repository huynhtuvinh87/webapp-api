<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Role;
use App\Models\Contractor;
use App\Models\Module;
use App\Models\ModuleVisibility;
use Tests\ModuleVisibilityBaseTestClass;

class ModuleVisibilityContractorTest extends ModuleVisibilityBaseTestClass
{
    /**
     * Testing Module visibility - Hiring Org relation
     *
     * @group ModuleVisibility
     * @group DynamicForms
     * @return void
     */
    public function testModuleVisContractorRelation()
    {
        // Checking visibility given hiring org
        $this->assertNotNull(static::$contractor->moduleVisibility);
        $this->assertGreaterThan(0, sizeof(static::$contractor->moduleVisibility));
    }

    /**
     * Testing Hiring Org method: isModuleVisible
     *
     * @group ModuleVisibility
     * @group DynamicForms
     * @return void
     */
    public function testContractorVisMethodById()
    {
        // Checking visibility of specific module
        $roleVis = static::$contractor->isModuleVisible(static::$module->id);
        $this->assertNotNull($roleVis);
        $this->assertEquals($roleVis, static::$moduleVisContractor['visible']);
    }

    /**
     * Testing Hiring Org method: isModuleVisible using module name
     *
     * @group ModuleVisibility
     * @group DynamicForms
     * @return void
     */
    public function testContractorVisMethodByName()
    {
        // Getting visibility by name
        $visByName = static::$contractor->isModuleVisible(Module::where('name', static::$module->name)->first()->id);

        $this->assertNotNull($visByName);
        $this->assertEquals($visByName, static::$moduleVisContractor['visible']);
    }
}
