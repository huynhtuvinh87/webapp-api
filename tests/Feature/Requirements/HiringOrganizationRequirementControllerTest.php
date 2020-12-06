<?php

namespace Tests\Feature;

use App\Lib\Services\HiringOrganizationCompliance;
use App\Models\HiringOrganization;
use App\Models\Role;
use App\Models\User;
use Exception;
use Tests\TestCase;
use App\Http\Controllers\Api\HiringOrganizationRequirementController;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Log;

class HiringOrganizationRequirementControllerTest extends TestCase
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    /**
     * Testing the ability to create Requirements through the store() method
     */
    public function testStoreRoute(){

        $route = '/api/organization/requirement/';
        $user = factory(User::class)->create();
        $hiringOrg = factory(HiringOrganization::class)->create();

        $role = factory(Role::class)->create([
            'entity_id' => $hiringOrg->id,
            'entity_key' => 'hiring_organization',
            'access_level' => 4,
            'role' => 'owner',
            'user_id' => $user->id
        ]);

        // Verifying role is attached to user object
        $rolesCount = $user->roles()->count();
        $this->assertEquals(1, $rolesCount);

        $response = $this->actingAs($user, 'api')
            ->json("POST", $route);
        $this->markTestIncomplete();

        $this->assertEquals(200, $response->status(), "Should be able to upload data");
        $this->assertNotNull($response);

        // $roleVisibilityResponse->assertJsonStructure([
        //     'role_visibilities',
        //     'hiring_organization_visibilities',
        //     'contractor_visibilities',
        // ]);
        Log::debug(__METHOD__, [
            'response' => $response
        ]);

    }
}
