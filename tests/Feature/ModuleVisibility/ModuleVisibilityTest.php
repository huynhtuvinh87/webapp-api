<?php

namespace Tests\Feature;

use App\Models\HiringOrganization;
use App\Models\Module;
use App\Models\ModuleVisibility;
use Tests\ModuleVisibilityBaseTestClass;

class ModuleVisibilityTest extends ModuleVisibilityBaseTestClass
{
    /**
     * Testing module reading
     *
     * @group ModuleVisibility
     * @group DynamicForms
     * @return void
     */
    public function testReadModule()
    {
        $this->assertNotNull(static::$module);
        $this->assertNotNull(static::$module->id);
        $this->assertNotNull(static::$module->name);
        $this->assertNotNull(static::$module->visible);
    }

    /**
     * Testing creation of a module
     *
     * @group ModuleVisibility
     * @group DynamicForms
     * @return void
     */
    public function testCreateModule()
    {
        $module = factory(Module::class)->create();

        $this->assertNotNull($module);
        $this->assertNotNull($module->id);
        $this->assertNotNull($module->name);
        $this->assertNotNull($module->visible);
    }
    /**
     * Testing creation of a module visibility
     *
     * @group ModuleVisibility
     * @group DynamicForms
     * @return void
     */
    public function testCreateModuleVisibility()
    {
        $hiringOrg = HiringOrganization::first();
        $module = factory(Module::class)->create();

        $moduleVis = factory(ModuleVisibility::class)->create([
            'module_id' => $module->id,
            'entity_type' => 'hiring_organization',
            'entity_id' => $hiringOrg->id,
        ]);

        $this->assertNotNull($moduleVis);
        $this->assertNotNull($moduleVis->id);
        $this->assertNotNull($moduleVis->visible);
    }

    /**
     * Testing reading of a module visibility
     *
     * @group ModuleVisibility
     * @group DynamicForms
     * @return void
     */
    public function testReadModuleVisibility()
    {
        $this->assertNotNull(static::$moduleVis);
        $this->assertNotNull(static::$moduleVis->id);
        $this->assertNotNull(static::$moduleVis->visible);
    }

    /**
     * Testing Module visibility - Module relation
     *
     * @group ModuleVisibility
     * @group DynamicForms
     * @return void
     */
    public function testModuleVisModuleRelation()
    {

        // Checking relation between visibility and module
        $this->assertNotNull(static::$moduleVis->module);
        $this->assertEquals(static::$moduleVis->module->id, static::$module->id);
    }
}
