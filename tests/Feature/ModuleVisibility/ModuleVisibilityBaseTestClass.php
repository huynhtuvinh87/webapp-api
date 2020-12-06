<?php

namespace Tests;

use App\Models\HiringOrganization;
use App\Models\Module;
use App\Models\ModuleVisibility;
use App\Models\Role;
use App\Models\Contractor;
use Exception;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class ModuleVisibilityBaseTestClass extends BaseTestCase
{
    use CreatesApplication;

    protected static $isInit = false;
    protected static $module = null;
    protected static $moduleVis = null;
    protected static $moduleVisRole = null;
    protected static $moduleVisContractor = null;
    protected static $hiringOrg = null;
    protected static $contractor = null;
    protected static $role_hiringOrg = null;
    protected static $moduleRoute = '/api/module';

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    public function setUp(): void
    {
        parent::setUp();

        if (!static::$isInit) {

            // Getting user / company information
            static::$hiringOrg = HiringOrganization::first();
            static::$contractor = Contractor::first();
            static::$role_hiringOrg = Role::where('entity_key', 'hiring_organization')
                ->where('role', 'owner')
                ->where('entity_id', static::$hiringOrg->id)
                ->first();
            if (!isset(static::$role_hiringOrg)) {
                throw new Exception("Hiring Org Role could not be found!");
            }

            // Creating Module and visibility rules
            static::$module = factory(Module::class)->create();
            static::$moduleVis = factory(ModuleVisibility::class)->create([
                'module_id' => static::$module->id,
                'entity_type' => 'hiring_organization',
                'entity_id' => static::$hiringOrg->id,
            ]);
            static::$moduleVisRole = factory(ModuleVisibility::class)->create([
                'module_id' => static::$module->id,
                'entity_type' => 'role',
                'entity_id' => static::$role_hiringOrg->id,
            ]);
            static::$moduleVisContractor = factory(ModuleVisibility::class)->create([
                'module_id' => static::$module->id,
                'entity_type' => 'contractor',
                'entity_id' => static::$contractor->id,
            ]);

            static::$isInit = true;
        }
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
