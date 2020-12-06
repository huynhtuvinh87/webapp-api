<?php

namespace HiringOrganization\Dashboard;

use App\Models\HiringOrganization;
use App\Models\User;
use App\ViewModels\ViewContractorRequirements;
use App\ViewModels\ViewEmployeeRequirements;
use Exception;
use Log;
use Tests\TestCase;

class HiringOrganizationDashboardControllerTest extends TestCase
{
    /**
     * Checks the overall compliance route and verifies the top level json structure of the response
     *
     * @return void
     * @throws Exception
     */
    public function testOverallComplianceRoute()
    {
        $user = User::first();
        $route = '/api/organization/dashboard/';

        if (!isset($user)) {
            throw new Exception("User was not defined");
        }

        $response = $this->actingAs($user, 'api')
            ->json("GET", $route);

        $this->assertEquals(200, $response->status());
        $this->assertNotNull($response);

        $response->assertJsonStructure([
            'contractor_compliance',
            'employee_compliance',
            'contractors',
        ]);
    }

    /*
     * Tests HO with CORPORATE Internal Requirements in Warning
     */
    public function testWarningInternalRequirements()
    {
        //Corporate
        $route = '/api/organization/dashboard/warning-internal-requirements';

        $internal_requirement_warning = ViewContractorRequirements::where('requirement_type', 'internal_document')
            ->whereNotNull('completion_date')
            ->inRandomOrder()
            ->limit(1)
            ->first();


        if (isset($internal_requirement_warning)) {

            $this->assertInstanceOf(ViewContractorRequirements::class, $internal_requirement_warning);

            $hiring_organization = HiringOrganization::find($internal_requirement_warning->hiring_organization_id);
            $this->assertInstanceOf(HiringOrganization::class, $hiring_organization);

            $ho_user = User::find($hiring_organization->owner->user_id);
            $this->assertInstanceOf(User::class, $ho_user);

            $response = $this->actingAs($ho_user, 'api')
                ->json("GET", $route);

            log::info(json_encode($response));

            $response->assertStatus(200);
            $this->assertNotNull($response);

        } else {
            $this->assertNull($internal_requirement_warning);
        }
    }

    /*
     * Tests HO with EMPLOYEE Internal Requirements in Warning
     */
    public function testWarningEmployeeInternalRequirements()
    {
        //Employee
        $route = '/api/organization/dashboard/employee/warning-internal-requirements';

        $internal_requirement_warning = ViewEmployeeRequirements::where('requirement_type', 'internal_document')
            ->whereNotNull('completion_date')
            ->inRandomOrder()
            ->limit(1)
            ->first();


        if (isset($internal_requirement_warning)) {

            $this->assertInstanceOf(ViewEmployeeRequirements::class, $internal_requirement_warning);

            $hiring_organization = HiringOrganization::find($internal_requirement_warning->hiring_organization_id);
            $this->assertInstanceOf(HiringOrganization::class, $hiring_organization);

            $ho_user = User::find($hiring_organization->owner->user_id);
            $this->assertInstanceOf(User::class, $ho_user);

            $response = $this->actingAs($ho_user, 'api')
                ->json("GET", $route);

            $response->assertStatus(200);
            $this->assertNotNull($response);

        } else {
            $this->assertNull($internal_requirement_warning);
        }
    }

    /*
     * Tests for HO with no Internal Requirements
     */
    public function testHiringOrganizationWithNoInternalRequirements()
    {
        try {
            $route = '/api/organization/dashboard/warning-internal-requirements';

            $internal_requirement_warning = ViewContractorRequirements::where('requirement_type', '!=', 'internal_document')
                ->whereNotNull('completion_date')
                ->inRandomOrder()
                ->limit(1)
                ->first();


            if (isset($internal_requirement_warning)) {

                $this->assertInstanceOf(ViewContractorRequirements::class, $internal_requirement_warning);

                $hiring_organization = HiringOrganization::find($internal_requirement_warning->hiring_organization_id);
                $this->assertInstanceOf(HiringOrganization::class, $hiring_organization);

                $ho_user = User::find($hiring_organization->owner->user_id);
                $this->assertInstanceOf(User::class, $ho_user);

                $response = $this->actingAs($ho_user, 'api')
                    ->json("GET", $route);

                $response->assertStatus(200);
                $this->assertEmpty($response->original['warning_requirements']);

            } else {
                $this->assertNull($internal_requirement_warning);
            }
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}
