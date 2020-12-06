<?php

namespace Tests\Feature;

use App\Models\HiringOrganization;
use App\Models\User;
use Exception;
use Tests\TestCase;

class EmployeeDashboardControllerTest extends TestCase
{

    private static $isInit = false;
    private static $hiringOrg = null;
    private static $contractor = null;
    private static $contractorEmployee = null;

    public function setUp(): void
    {
        parent::setUp();
        if (!static::$isInit) {
            // Getting sample role using ALC Schools
            // NOTE: ALC Schools ID = 144
            static::$hiringOrg = HiringOrganization::find(144);
            $hiringOrgName = "ALC Schools";
            if (!isset(static::$hiringOrg)) {
                throw new Exception("Hiring Org was not defined");
            }
            if (static::$hiringOrg->name != $hiringOrgName) {
                throw new Exception("Could not find $hiringOrgName. Instead got: " . static::$hiringOrg->name);
            }

            // Getting contractor employee
            static::$contractor = static::$hiringOrg
                ->contractors()
                ->first();
            if (!isset(static::$contractor)) {
                throw new Exception("Contractor could not be found");
            }

            // Getting Contractor Employee
            static::$contractorEmployee = static::$contractor
                ->roles()
                ->where('role', 'employee')
                ->first();
            if (!isset(static::$contractorEmployee)) {
                throw new Exception("Contractor employee could not be found");
            }

            static::$isInit = true;
        }
    }

    /**
     * Test for companyRequirements() method
     *
     *
     * @return void
     */
    public function testCompanyRequirementsAsEmployee()
    {
        $response = $this->getCompanyRequirementsResponse(static::$contractorEmployee->user, static::$hiringOrg);

        // Testing response
        $this->assertEquals(200, $response->status());
        $response->assertJsonStructure([
            'compliance',
            'requirements',
        ]);

        $responseObj = json_decode($response->content());

        // Checking requirements
        $this->assertNotCount(0, $responseObj->requirements, "Has more than 1 requirement");
    }

    /**
     * Test to check to see that internal requirements are showing up in the requirements call
     *
     * @group InternalDocuments
     * @return void
     */
    public function testCompanyRequirementsForInternalRequirements()
    {
        $response = $this->getCompanyRequirementsResponse(static::$contractorEmployee->user, static::$hiringOrg);

        $responseObj = json_decode($response->content());

        // Checking if there are any internal documents
        // NOTE: DEV-1245 - we're including internal requirements to be visible, but files are not visible
        // NOTE: ALC Schools will 100% have internal requirements
        $internalRequirements = collect($responseObj->requirements)
            ->filter(function ($requirement) {
                return $requirement->requirement_type == 'internal' || $requirement->requirement_type == 'internal_document';
            });

        $this->markTestSkipped("Need to setup a proper dummy account to test the internal requirements on");
        $this->assertNotCount(0, $internalRequirements);
    }

    public function testPastDueRequirements()
    {
        $employeeUser = static::$contractorEmployee->user;

        $response = $this->getPastDueRequirements($employeeUser);

        $this->assertEquals(200, $response->status());

        $response->assertJsonStructure([
            'past_due',
        ]);

        $responseObj = json_decode($response->content());

        // Verifying there is more than one past_due requirement
        $this->assertNotCount(0, $responseObj->past_due, "More than 1 past_due requirement");

    }

    /**
     * @group InternalDocuments
     * @return void
     */
    public function testPastDueRequirementsHaveInternalDocuments()
    {
        $employeeUser = static::$contractorEmployee->user;

        $response = $this->getPastDueRequirements($employeeUser);

        $responseObj = json_decode($response->content());

        $internalDocumentReqs = collect($responseObj->past_due)
            ->filter(function($requirement){
                return $requirement->requirement_type == 'internal' || $requirement->requirement_type == 'internal_document';
            });

        $this->markTestSkipped("Need to setup a proper dummy account to test the internal requirements on");
        $this->assertNotCount(0, $internalDocumentReqs);
    }

    /**
     * Test to see if a contractor can get a file_id through the history
     * The file_id should not be passed over
     *
     * @group InternalDocuments
     * @return void
     */
    public function testInternalDocAccessThroughHistory(){
        $this->markTestIncomplete();
    }

   /**
    * Test to see if a contractor can access a file if they get a hold of the file_id of a file associated with an internal document
    * The user should not be able to access that file
    *
    * @group InternalDocuments
    * @return void
    */
    public function testInternalDocAccessThroughFileId()
    {
        $this->markTestIncomplete();
    }

    /* ---------------------------- Helper Functions ---------------------------- */

    private function getCompanyRequirementsResponse(User $employeeUser, HiringOrganization $hiringOrg)
    {
        $hiringOrgId = $hiringOrg->id;
        $response = $this->actingAs($employeeUser, 'api')
            ->json("GET", "api/employee/company-compliance/$hiringOrgId/requirements");


        return $response;
    }

    private function getPastDueRequirements(User $user)
    {
        $response = $this->actingAs($user, 'api')
            ->json("GET", "api/employee/past-due-requirements");

        return $response;
    }
}
