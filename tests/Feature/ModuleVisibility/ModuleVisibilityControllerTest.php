<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\ModuleVisibility;
use App\Models\Role;
use Tests\ModuleVisibilityBaseTestClass;

class ModuleVisibilityControllerTest extends ModuleVisibilityBaseTestClass
{
    /**
     * Test for all modules
     *
     * @group ModuleVisibility
     * @group DynamicForms
     * @return void
     */
    public function testModuleControllerAllModules()
    {
        $user = static::$role_hiringOrg->user;
        $route = static::$moduleRoute ;

        $this->assertNotNull($route);
        $this->assertEquals('/api/module', $route);

        $roleVisibilityResponse = $this->actingAs($user, 'api')
            ->json("GET", $route);

        $this->assertEquals(200, $roleVisibilityResponse->status());
        $this->assertNotNull($roleVisibilityResponse);

        $roleVisibilityResponse->assertJsonStructure([
            'modules',
        ]);
    }

    /**
     * Testing module controller - role visibility
     *
     * @group ModuleVisibility
     * @group DynamicForms
     * @return void
     */
    public function testModuleControllerRoleVisibility()
    {
        $user = static::$role_hiringOrg->user;
        $route = static::$moduleRoute . '/visibility/role';

        $this->assertNotNull($route);
        $this->assertEquals('/api/module/visibility/role', $route);

        $roleVisibilityResponse = $this->actingAs($user, 'api')
            ->json("GET", $route);

        $this->assertEquals(200, $roleVisibilityResponse->status());
        $this->assertNotNull($roleVisibilityResponse);

        $roleVisibilityResponse->assertJsonStructure([
            'role_visibilities',
        ]);
    }

    /**
     * Testing module controller - all visibility
     *
     * @group ModuleVisibility
     * @group DynamicForms
     * @return void
     */
    public function testModuleControllerAllVisibility()
    {
        $user = static::$role_hiringOrg->user;
        $route = static::$moduleRoute . '/visibility';

        $this->assertNotNull($route);
        $this->assertEquals('/api/module/visibility', $route);

        $roleVisibilityResponse = $this->actingAs($user, 'api')
            ->json("GET", $route);

        $this->assertEquals(200, $roleVisibilityResponse->status());
        $this->assertNotNull($roleVisibilityResponse);

        $roleVisibilityResponse->assertJsonStructure([
            'role_visibilities',
            'hiring_organization_visibilities',
            'contractor_visibilities',
        ]);
    }

    /**
     * Testing module controller - all visibility by module
     *
     * @group ModuleVisibility
     * @group DynamicForms
     * @return void
     */
    public function testModuleControllerAllVisibilityForModule()
    {
        $user = static::$role_hiringOrg->user;
        $route = static::$moduleRoute . '/visibility/' . static::$module->id;

        $this->assertNotNull($route);

        $roleVisibilityResponse = $this->actingAs($user, 'api')
            ->json("GET", $route);

        $this->assertEquals(200, $roleVisibilityResponse->status());
        $this->assertNotNull($roleVisibilityResponse);

        $roleVisibilityResponse->assertJsonStructure([
            'role_visibilities',
            'hiring_organization_visibilities',
            'contractor_visibilities',
        ]);
    }
}
