<?php

namespace Tests\Feature;

use App\Models\ExclusionRequest;
use App\Models\HiringOrganization;
use App\Models\Requirement;
use App\Models\RequirementHistory;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ContractorDashboardControllerTest extends TestCase
{
    use DatabaseTransactions;

    private static $isInit = false;
    private static $hiringOrg = null;
    private static $contractor = null;
    private static $contractorEmployee = null;
    private static $contractorOwner = null;

    public function setUp(): void
    {
        parent::setUp();
        if (!static::$isInit) {
            // Getting sample role using ALC Schools
            // NOTE: ALC Schools ID = 144
            static::$hiringOrg = HiringOrganization::find(144);
            $hiringOrgName = "ALC Schools";
            if (!isset(static::$hiringOrg)) {
                $this->markTestSkipped("Hiring Org was not defined");
            }
            if (static::$hiringOrg->name != $hiringOrgName) {
                $this->markTestSkipped("Could not find $hiringOrgName. Instead got: " . static::$hiringOrg->name);
            }

            // Getting contractor employee
            static::$contractor = static::$hiringOrg
                ->contractors()
                ->first();
            if (!isset(static::$contractor)) {
                $this->markTestSkipped("Contractor could not be found");
            }

            // Getting Contractor Employee
            static::$contractorEmployee = static::$contractor
                ->roles()
                ->where('role', 'employee')
                ->first();
            if (!isset(static::$contractorEmployee)) {
                $this->markTestSkipped("Contractor employee could not be found");
            }

            // Getting Contractor Owner
            static::$contractorOwner = static::$contractor->owner;
            if (!isset(static::$contractorOwner)) {
                $this->markTestSkipped("Contractor owner could not be found");
            }

            static::$isInit = true;
        }

        // Ensuring there is an internal document for the corporate level positions
        // Get internal requirement
        $internalRequirement = Requirement::where([
            'hiring_organization_id' => static::$hiringOrg->id,
            'type' => 'internal_document',
        ])
            ->first();

        if (!isset($internalRequirement)) {
            throw new Exception("Internal Requirement could not be found");
        }

        // Get all positions
        // attach internal requirement to positions
        static::$hiringOrg->positions
            ->each(function ($position) use ($internalRequirement) {
                $internalRequirement->positions()->syncWithoutDetaching([$position->id]);
            });
    }

    /**
     * Test for companyRequirements() method
     *
     *
     * @return void
     */
    public function testCompanyRequirements()
    {
        $response = $this->getCompanyRequirementsResponse(static::$contractorOwner->user, static::$hiringOrg);

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
     *
     * @return void
     */
    public function testCompanyRequirementsForInternalRequirements()
    {
        $response = $this->getCompanyRequirementsResponse(static::$contractorOwner->user, static::$hiringOrg);

        $responseObj = json_decode($response->content());

        // Checking if there are any internal documents
        // NOTE: DEV-1245 - we're including internal requirements to be visible, but files are not visible
        // NOTE: ALC Schools will 100% have internal requirements
        $internalRequirements = collect($responseObj->requirements)
            ->filter(function ($requirement) {
                return $requirement->requirement_type == 'internal' || $requirement->requirement_type == 'internal_document';
            });

        $this->assertNotCount(0, $internalRequirements, 'Should have at least 1 internal requirement');
    }

    public function testPastDueRequirements()
    {
        $user = static::$contractorOwner->user;

        $response = $this->getPastDueRequirements($user);

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
     *
     * @return void
     */
    public function testPastDueRequirementsHaveInternalDocuments()
    {
        $user = static::$contractorOwner->user;

        $response = $this->getPastDueRequirements($user);

        $responseObj = json_decode($response->content());

        $internalDocumentReqs = collect($responseObj->past_due)
            ->filter(function ($requirement) {
                return $requirement->requirement_type == 'internal' || $requirement->requirement_type == 'internal_document';
            });

        $this->markTestSkipped("Need to setup a proper dummy account to test the internal requirements on");
        $this->assertNotCount(0, $internalDocumentReqs);
    }

    /**
     * @group InternalDocuments
     *
     * @return void
     */
    public function testShouldNotBeAbleToRequestExclusionForInternalDoc()
    {
        // Finding internal doc
        $requirements = static::$contractor->requirements
            ->where('hiring_organization_id', static::$hiringOrg->id)
            ->where('requirement_type', 'internal_document')
            ->values()
            ->all();

        if (sizeof($requirements) == 0) {
            throw new Exception("Internal Requirements not found for " . static::$contractor->name);
        }

        $requirement = $requirements[0];

        if (!isset($requirement)) {
            throw new Exception("Requirement was undefined");
        }
        if (!isset($requirement->requirement_id)) {
            throw new Exception("Requirement had no ID");
        }

        $user = static::$contractorOwner->user;

        // Checking exclusions before
        $exclusions = ExclusionRequest::where([
            'requirement_id' => $requirement->requirement_id,
            'requester_role_id' => $user->role,
            'contractor_id' => static::$contractor->id,
        ])
            ->get();

        if (sizeof($exclusions) != 0) {
            throw new Exception("Already has exclusions");
        }

        $response = $this->actingAs($user, 'api')
            ->json(
                "POST",
                "api/contractor/dashboard/requirements/request-exclusion",
                [
                    'note' => "This is a test",
                    "requirement_id" => $requirement->requirement_id,
                ]
            );

        // Verifying that an error was returned when trying to request an exclusion
        $this->assertNotEquals(200, $response->status());
        $this->assertNotEquals(422, $response->status(), '422 for exclusion already requested');
        $this->assertEquals(403, $response->status());

        // Verifying record is not in database
        $exclusions = ExclusionRequest::where([
            'requirement_id' => $requirement->id,
            'requester_role_id' => $user->role,
            'contractor_id' => static::$contractor->id,
        ])
            ->get();

        $this->assertCount(0, $exclusions);
    }

    /**
     * Test to ensure a contractor can't access the internal document submission
     * ContractorDashboardController@requirementHistories
     *
     * @group InternalDocuments
     * @return void
     */
    public function testShouldNotBeAbleToAccessInternalDocSubmission()
    {
        // Find internal requirement with history
        $reqHistRes = DB::table('requirement_histories')
            ->join('requirements', 'requirements.id', '=', 'requirement_histories.requirement_id')
            ->select([
                'requirement_histories.*',
            ])
            ->where('requirements.type', 'internal_document')
            ->limit(10)
            ->first();

        if (!isset($reqHistRes)) {
            throw new Exception("Could not find requirement history entry - query returned nothing");
        }

        // Find role that the requirement is associated to
        $reqHis = RequirementHistory::find($reqHistRes->id);
        $requirement = $reqHis->requirement;

        $role = $reqHis->role;
        if (!isset($role)) {
            throw new Exception("Could not get role associated with Requirement History");
        }

        // Try accessing file through controller
        $historyResponse = $this->actingAs($role->user, 'api')
            ->json(
                "GET",
                "api/contractor/dashboard/requirements/$requirement->id/history",
            );
        $historyResponse->assertStatus(200);

        // Through history, get file ID
        $responseObj = json_decode($historyResponse->content());
        $this->assertNotNull($responseObj);
        $this->assertNotCount(0, $responseObj, 'At least 1 history record was returned');

        // If the attribute is set, ensure its null
        if (isset($responseObj[0]->file_id)) {
            $this->assertNull($responseObj[0]->file_id);
        }
    }

    /**
     * Test to see if a contractor can access a file if they get a hold of the file_id of a file associated with an internal document
     * The user should not be able to access that file
     *
     * @group InternalDocuments
     * @return void
     */
    public function testTryToAccessFileThatIsInternal()
    {
        $this->markTestIncomplete();
    }

    /* ---------------------------- Helper Functions ---------------------------- */

    private function getCompanyRequirementsResponse(User $employeeUser, HiringOrganization $hiringOrg)
    {
        $hiringOrgId = $hiringOrg->id;
        $response = $this->actingAs($employeeUser, 'api')
            ->json("GET", "api/contractor/dashboard/company-compliance/$hiringOrgId/requirements");

        return $response;
    }

    private function getPastDueRequirements(User $user)
    {
        $response = $this->actingAs($user, 'api')
            ->json("GET", "api/contractor/dashboard/past-due-requirements");

        return $response;
    }
}
