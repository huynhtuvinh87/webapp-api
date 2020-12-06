<?php

namespace Tests\Feature;

use App\Lib\Services\HiringOrganizationComplianceV2;
use App\Models\Contractor;
use App\Models\ExclusionRequest;
use App\Models\Facility;
use App\Models\HiringOrganization;
use App\Models\Position;
use App\Models\Requirement;
use App\Models\RequirementContent;
use App\Models\RequirementHistory;
use App\Models\RequirementHistoryReview;
use App\Models\Resource;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Log;
use Tests\TestCase;

class HiringOrganizationCompliance extends TestCase
{
    // use DatabaseTransactions;

    protected static $isInit = false;

    protected static $hiringOrg = null;
    protected static $hiringOrgOwnerRole = null;
    protected static $hiringOrgOwnerUser = null;

    protected static $contractor = null;
    protected static $contractorOwnerRole = null;
    protected static $contractorEmployeeRole = null;
    protected static $contractorOwnerUser = null;

    protected static $contractorsWithComplianceRes = [];

    /* ---------------------------------- Setup --------------------------------- */

    public function setUp(): void
    {
        parent::setUp();

        if (!static::$isInit) {
            Storage::put('testquery.sql', "-- TEST QUERY  --\n\n");
            Storage::put('testquery2.sql', "-- TEST QUERY  --\n\n");

            /* ------------------------------- Hiring Org ------------------------------- */

            // Creating Hiring Org
            static::$hiringOrg = factory(HiringOrganization::class)->create([
                'name' => "Test Hiring Organization",
            ]);
            // Creating Hiring Org User
            static::$hiringOrgOwnerUser = factory(User::class)->create([
                "first_name" => static::$hiringOrg->name,
                "last_name" => "Owner",
            ]);
            // Creating Hiring Org Owner Role
            static::$hiringOrgOwnerRole = factory(Role::class)->create([
                "role" => "owner",
                "user_id" => static::$hiringOrgOwnerUser->id,
                "entity_key" => "hiring_organization",
                "entity_id" => static::$hiringOrg->id,
            ]);
            static::$hiringOrgOwnerUser->update([
                'current_role_id' => static::$hiringOrgOwnerRole->id,
            ]);

            /* --------------------------- Creating Contractor -------------------------- */

            // Creating Hiring Org
            static::$contractor = factory(Contractor::class)->create([
                'name' => "Test Contractor",
            ]);
            // Creating Hiring Org User
            static::$contractorOwnerUser = factory(User::class)->create([
                "first_name" => static::$contractor->name,
                "last_name" => "Owner",
            ]);
            // Creating Hiring Org Owner Role
            static::$contractorOwnerRole = factory(Role::class)->create([
                "role" => "owner",
                "user_id" => static::$contractorOwnerUser->id,
                "entity_key" => "contractor",
                "entity_id" => static::$contractor->id,
            ]);
            static::$contractorOwnerUser->update([
                'current_role_id' => static::$contractorOwnerRole->id,
            ]);

            // Creating Contractor Employee
            static::$contractorEmployeeRole = factory(Role::class)->create([
                "role" => "employee",
                "user_id" => static::$contractorOwnerUser->id,
                "entity_key" => "contractor",
                "entity_id" => static::$contractor->id,
            ]);

            /* ----------------- Facilities, Positions, and Requirements ---------------- */

            // // Creating Facilities
            // factory(Facility::class)->create([
            //     'hiring_organization_id' => static::$hiringOrg->id,
            //     'description' => "Created from " . __METHOD__,
            // ]);

            // // Creating Positions
            // $contractorPositions = factory(Position::class, 2)->create([
            //     'hiring_organization_id' => static::$hiringOrg->id,
            //     'is_active' => true,
            //     'position_type' => 'contractor',
            // ]);

            // // Creating Requirements
            // $requirements = factory(Requirement::class, 2)->create([
            //     'hiring_organization_id' => static::$hiringOrg->id,
            //     'warning_period' => 7,
            //     'renewal_period' => 12,
            // ]);
            // $requirements->each(function ($requirement) {
            //     factory(RequirementContent::class)->create([
            //         "requirement_id" => $requirement->id,
            //         'lang' => 'en',
            //         'name' => "Test Requirement " . $requirement->id,
            //         "text" => null,
            //     ]);
            // });

            // // Assigning positions to requirements
            // $contractorPositions->each(function ($position) use ($requirements) {
            //     $requirements->each(function ($requirement) use ($position) {
            //         $position->requirements()->save($requirement);
            //     });
            // });

            /* ---------------------------------- Misc ---------------------------------- */

            // Connecting Hiring Org with Contractor
            static::$hiringOrg->contractors()->sync(static::$contractor, [
                'accepted' => true,
            ]);

            // Assigning positions with requirements to contractor
            // static::$contractor->positions()->sync($contractorPositions->first());

            static::$isInit = true;
        }
    }

    /**
     * @group HiringOrganizationCompliance
     */
    public function testSetup(): void
    {
        // Setup of hiring org
        $this->assertNotNull(static::$hiringOrg);
        $this->assertNotNull(static::$hiringOrgOwnerUser);
        $this->assertNotNull(static::$hiringOrgOwnerRole);

        // Setup of contractor
        $this->assertNotNull(static::$contractor);
        $this->assertNotNull(static::$contractorOwnerUser);
        $this->assertNotNull(static::$contractorOwnerRole);

        // Connection
        $hiringOrgContractors = static::$hiringOrg->contractors;
        $this->assertNotNull($hiringOrgContractors);
        $this->assertNotCount(0, $hiringOrgContractors);

        // // Facilities
        // $this->assertNotNull(static::$hiringOrg->facilities);
        // $this->assertNotCount(0, static::$hiringOrg->facilities);

        // // Positions
        // $this->assertNotNull(static::$hiringOrg->positions);
        // $this->assertNotCount(0, static::$hiringOrg->positions);

        // // Requirements
        // $this->assertNotNull(static::$hiringOrg->requirements);
        // $this->assertNotCount(0, static::$hiringOrg->requirements);
    }

    /* ---------------------------- Helper Functions ---------------------------- */

    public function createContractorWithRequirements()
    {

    }

    /* ---------------------------------- Tests --------------------------------- */

    public function testGetRequirementStatusesProvider()
    {
        return [
            // Not Submitted
            // Not auto approved
            // Not reviewed
            // No exclusion
            [
                "hiringOrgid" => null,
                "hasRequirementHistoryId" => false,
                "autoApproved" => false,
                "hasApprovedReview" => false,
                "hasExclusion" => false,

                "isSubmitted" => false,
                "isApproved" => false,
                "isCompleted" => false,
                "isExcluded" => false,
                "isActive" => true,
                "isActiveAndCompleted" => false,
            ],

            // Submitted
            // Not auto approved
            // Not reviewed
            // No exclusion
            [
                "hiringOrgId" => null,
                "hasRequirementHistoryId" => true,
                "autoApproved" => false,
                "hasApprovedReview" => false,
                "hasExclusion" => false,

                "isSubmitted" => true,
                "isApproved" => false,
                "isCompleted" => false,
                "isExcluded" => false,
                "isActive" => true,
                "isActiveAndCompleted" => false,
            ],

            // Submitted
            // Auto approved
            // Not reviewed
            [
                "hiringOrgId" => null,
                "hasRequirementHistoryId" => true,
                "autoApproved" => true,
                "hasApprovedReview" => false,
                "hasExclusion" => false,

                "isSubmitted" => true,
                "isApproved" => false,
                "isCompleted" => true,
                "isExcluded" => false,
                "isActive" => true,
                "isActiveAndCompleted" => true,
            ],

            // Submitted
            // Not auto approved
            // Reviewed
            [
                "hiringOrgId" => null,
                "hasRequirementHistoryId" => true,
                "autoApproved" => false,
                "hasApprovedReview" => true,
                "hasExclusion" => false,

                "isSubmitted" => true,
                "isApproved" => true,
                "isCompleted" => true,
                "isExcluded" => false,
                "isActive" => true,
                "isActiveAndCompleted" => true,
            ],

            // Not Submitted
            // Exclusion Request
            [
                "hiringOrgId" => null,
                "hasRequirementHistoryId" => false,
                "autoApproved" => false,
                "hasApprovedReview" => false,
                "hasExclusion" => true,

                "isSubmitted" => false,
                "isApproved" => false,
                "isCompleted" => false,
                "isExcluded" => true,
                "isActive" => false,
                "isActiveAndCompleted" => false,
            ],

            // Testing Live Hiring Orgs

            // Not Submitted
            // Exclusion Request
            [
                "hiringOrgId" => 85,
                "hasRequirementHistoryId" => false,
                "autoApproved" => false,
                "hasApprovedReview" => false,
                "hasExclusion" => true,

                "isSubmitted" => false,
                "isApproved" => false,
                "isCompleted" => false,
                "isExcluded" => true,
                "isActive" => false,
                "isActiveAndCompleted" => false,
            ],

            // Not Submitted
            // Exclusion Request
            [
                "hiringOrgId" => 69,
                "hasRequirementHistoryId" => false,
                "autoApproved" => false,
                "hasApprovedReview" => false,
                "hasExclusion" => true,

                "isSubmitted" => false,
                "isApproved" => false,
                "isCompleted" => false,
                "isExcluded" => true,
                "isActive" => false,
                "isActiveAndCompleted" => false,
            ],
        ];
    }

    /**
     * @group HiringOrganizationCompliance
     * @small
     * @dataProvider testGetRequirementStatusesProvider
     * @testdox Requirement Status: Submitted $hasRequirementHistoryId, Auto Approved $autoApproved, Reviewed $hasApprovedReview, Excluded $hasExclusion
     */
    public function testGetRequirementStatuses(
        $hiringOrgId,
        $hasRequirementHistoryId,
        $autoApproved,
        $hasApprovedReview,
        $hasExclusion,
        $isSubmitted,
        $isApproved,
        $isCompleted,
        $isExcluded,
        $isActive,
        $isActiveAndCompleted
    ) {
        // Creating entities
        if (isset($hiringOrgId)) {
            $hiringOrg = HiringOrganization::find($hiringOrgId);
        } else {

            // $hiringOrg = factory(HiringOrganization::class)->create([
            //     'name' => __FUNCTION__,
            // ]);
            $hiringOrg = static::$hiringOrg;
            $contractor = factory(Contractor::class)->create([
                'name' => __FUNCTION__,
            ]);
            $contractorOwnerRole = factory(Role::class)->create([
                'user_id' => static::$contractorOwnerUser->id,
                'entity_key' => "contractor",
                "entity_id" => $contractor->id,
                "role" => "owner",
            ]);
            $facility = factory(Facility::class)->create([
                'name' => __FUNCTION__,
                'hiring_organization_id' => $hiringOrg->id,
            ]);
            $position = factory(Position::class)->create([
                'name' => __FUNCTION__,
                'hiring_organization_id' => $hiringOrg->id,
                "position_type" => 'contractor',
                'is_active' => true,
            ]);
            $requirement = factory(Requirement::class)->create([
                'hiring_organization_id' => $hiringOrg->id,
                'type' => "upload",
                "count_if_not_approved" => $autoApproved == true,
            ]);
            // $requirementContent = factory(RequirementContent::class)->create([
            //     'requirement_id' => $requirement->id,
            //     'name' => __FUNCTION__
            // ]);

            // Creating Connections
            $hiringOrg->contractors()->sync($contractor);
            $contractor->positions()->sync($position);
            $contractor->facilities()->sync($facility);
            $facility->positions()->sync($position);
            $position->requirements()->sync($requirement);
            $contractorOwnerRole->positions()->sync($position);

            // Is Submitted
            if ($isSubmitted) {
                $requirementHistory = factory(RequirementHistory::class)->create([
                    'contractor_id' => $contractor->id,
                    'requirement_id' => $requirement->id,
                ]);

                if ($isApproved) {
                    $requirementHistoryReview = factory(RequirementHistoryReview::class)->create([
                        'requirement_history_id' => $requirementHistory->id,
                        'approver_id' => $hiringOrg->owner->id,
                    ]);
                }
            }

            if ($isExcluded) {
                $exclusionRequest = factory(ExclusionRequest::class)->create([
                    'requirement_id' => $requirement->id,
                    'contractor_id' => $contractor->id,
                    'status' => 'approved',
                    'requester_role_id' => $contractorOwnerRole->id,
                    "response_role_id" => $hiringOrg->owner->id,
                ]);
            }
        }

        $query = HiringOrganizationComplianceV2::getRequirementStatuses($hiringOrg);

        try {

            $this->assertNotNull($query);

            if ($hasRequirementHistoryId) {
                $query->whereNotNull('requirement_histories.id');
            } else {
                $query->whereNull('requirement_histories.id');
            }

            if ($hasApprovedReview) {
                $query
                    ->whereNotNull('requirement_history_reviews.id')
                    ->where("requirement_history_reviews.status", DB::raw("'approved'"));
            } else {
                $query->whereNull('requirement_history_reviews.id');
            }

            if ($hasExclusion) {
                $query
                    ->whereNotNull('exclusion_requests.id')
                    ->where("exclusion_requests.status", DB::raw("'approved'"));
            } else {
                $query->whereNull('exclusion_requests.id');
            }

            $query->where('count_if_not_approved', DB::raw("'$autoApproved'"));

            $requirementStatus = $query->first();

            if (!isset($requirementStatus)) {
                $this->markTestSkipped("No requirements found");
            }

            // Checking requirement history id column
            if ($hasRequirementHistoryId) {
                $this->assertNotNull($requirementStatus->requirement_history_id, "Expected submission");
            } else {
                $this->assertNull($requirementStatus->requirement_history_id, "Expected no submission");
            }

            $this->assertEquals($isSubmitted, $requirementStatus->is_submitted, "Submission status does not match");
            $this->assertEquals($isApproved, $requirementStatus->is_approved, "Approved status does not match");
            $this->assertEquals($isExcluded, $requirementStatus->is_excluded, "Exclusion status does not match. is_excluded should be $isExcluded");
            $this->assertEquals($isCompleted, $requirementStatus->is_completed, "Completion status does not match");

            $this->assertEquals($isActive, $requirementStatus->is_active, "Active status does not match");
            $this->assertEquals($isActiveAndCompleted, $requirementStatus->is_active_and_completed, "Active and Completed status does not match");
        } catch (Exception $e) {
            $errorMsg = str_replace("\n", " - ", $e->getMessage());
            $sqlFileContent = "\n\n-- " . $errorMsg . "\n\n" . $query->toSql() . ";";
            Storage::put(__FUNCTION__ . ".sql", $sqlFileContent);
            Log::debug(__METHOD__, [
                "message" => $e->getMessage(),
                "Hiring Org" => $hiringOrg->id,
            ]);
            throw $e;
        }
    }

    public function requirementPositionTypesProvider()
    {
        $positionTypes = collect([
            'employee',
            'contractor',
            // 'resource',
        ]);

        $requirementTypes = collect([
            "review",
            "upload",
            "upload_date",
        ]);

        $autoApprovedOpts = collect([
            true,
            false,
        ]);

        $sets = $requirementTypes->flatMap(function ($requirementType) use ($positionTypes, $autoApprovedOpts) {
            return $positionTypes->flatMap(function ($positionType) use ($requirementType, $autoApprovedOpts) {
                return $autoApprovedOpts->map(function ($autoApproved) use ($positionType, $requirementType) {
                    return [
                        'requirement_type' => $requirementType,
                        'position_type' => $positionType,
                        "auto_approved" => $autoApproved,
                    ];
                });
            });
        })
            ->toArray();

        return $sets;
    }

    /**
     * @group HiringOrganizationCompliance
     * @dataProvider requirementPositionTypesProvider
     * @testdox Testing Assigned Requirement query: $requirementType, $positionType, Auto Approved: $autoApproved
     */
    public function testGetAssignedRequirementInfo($requirementType, $positionType, $autoApproved)
    {
        // Setup
        $contractor = factory(Contractor::class)->create();
        $hiringOrg = factory(HiringOrganization::class)->create();

        $hiringOrg->contractors()->attach($contractor, [
            'accepted' => true,
        ]);

        $contractorOwnerUser = factory(User::class)->create([
            'password' => "Test",
        ]);
        $contractorOwnerRole = factory(Role::class)->create([
            'user_id' => $contractorOwnerUser->id,
            'role' => "owner",
            'entity_key' => "contractor",
            "entity_id" => $contractor->id,
        ]);

        $contractorOwnerUser->update([
            'current_role_id' => $contractorOwnerRole->id,
        ]);
        $contractor->update([
            'owner_id' => $contractorOwnerRole->id,
        ]);

        // Creating a position and requirements for contractor
        $position = factory(Position::class)->create([
            'hiring_organization_id' => $hiringOrg->id,
            'position_type' => $positionType,
            'name' => __METHOD__,
            'is_active' => true,
        ]);
        $requirements = factory(Requirement::class, 2)->create([
            'hiring_organization_id' => $hiringOrg->id,
            "type" => $requirementType,
            "count_if_not_approved" => $autoApproved,
            "is_visible" => true,
        ]);
        $position->requirements()->attach($requirements);

        // Assigning position to contractor
        if ($positionType == 'contractor') {
            $contractor->positions()->attach($position);
        } else {
            $contractorEmployee = factory(Role::class)->create([
                'entity_key' => 'contractor',
                'entity_id' => $contractor->id,
                'role' => 'employee',
                'user_id' => $contractorOwnerUser->id,
            ]);

            $contractorEmployee->positions()->attach($position);
        }

        // Test

        $query = HiringOrganizationComplianceV2::getAssignedRequirementInfo($hiringOrg);

        // Contractor should be connected to hiring org, and have positions with requirements
        $queryForTest = DB::table(DB::raw("({$query->toSql()}) as tbl"))
            ->where('contractor_id', DB::raw($contractor->id));

        $res = $queryForTest->get();

        // The contractor should have some positions assigned to their account
        $this->assertNotNull($res);
        try {
            $this->assertNotCount(0, $res, "Expected at least 1 position assigned to their account.");
        } catch (Exception $e) {
            Log::debug(__METHOD__, [
                "message" => $e->getMessage(),
                'position' => $position->id,
                // 'requirement' => $requirements->id,
                'query where clause' => "contractor_id = " . static::$contractor->id . " AND position_id = $position->id AND requirement_id IN (" . implode(", ", $requirements->map(function ($r) {return $r->id;})->toArray()) . ")",
            ]);
            throw $e;
        }
    }

    /**
     * @group HiringOrganizationCompliance
     * @dataProvider requirementPositionTypesProvider
     * @testdox Requirement Compliance: $requirementType, $positionType, auto approved: $autoApproved
     */
    public function testIndividualRequirementCompliance($requirementType, $positionType, $autoApproved)
    {
        // ----- Setup ----- //

        // Creating contractor / hiring org
        $contractor = factory(Contractor::class)->create();
        $contractorEmployeeRole = factory(Role::class)->create([
            'entity_key' => 'contractor',
            'entity_id' => $contractor->id,
            'role' => "employee",
            'user_id' => static::$contractorOwnerUser->id,
        ]);
        $contractorOwnerRole = factory(Role::class)->create([
            'entity_key' => 'contractor',
            'entity_id' => $contractor->id,
            'role' => "owner",
            'user_id' => static::$contractorOwnerUser->id,
        ]);
        $hiringOrg = factory(HiringOrganization::class)->create();
        $hiringOrg->contractors()->save($contractor);

        // Creating position and requirement
        $position = factory(Position::class)->create([
            "name" => __METHOD__,
            'hiring_organization_id' => $hiringOrg->id,
            "position_type" => $positionType,
            "is_active" => true,
        ]);
        $requirements = factory(Requirement::class, 2)->create([
            "warning_period" => 7,
            "renewal_period" => 12,
            "hiring_organization_id" => $hiringOrg->id,
            "type" => $requirementType,
            "count_if_not_approved" => $autoApproved,
        ]);

        $resource = factory(Resource::class)->create([
            'contractor_id' => $contractor->id,
        ]);

        // Connecting requirements to position
        $position->requirements()->attach($requirements);

        // Assigning requirement to Contractor
        $contractor->positions()->save($position);

        // If employee position, assign to employee
        if ($positionType == 'employee') {
            $contractorEmployeeRole->positions()->save($position);
            $employeePositionsQuery = DB::table("position_role")
                ->where("position_id", $position->id)
                ->where("role_id", $contractorEmployeeRole->id)
                ->get();

            $this->assertCount(1, $employeePositionsQuery);
        } else if ($positionType == 'resource') {
            // Creating a new resource
            $resource->positions()->attach($position);
            $resource->roles()->attach($contractorEmployeeRole);
        }

        // Verifying position was added properly
        if ($contractor->positions->count() != 1) {
            throw new Exception("Position count was not 1");
        }

        // Verifying contractor is connected to hiring org
        if (!$hiringOrg->contractors()->where('contractor_id', $contractor->id)->get()) {
            throw new Exception("Contractor was not connected to hiring org");
        }

        // ----- Test ----- //

        // Get Compliance for Contractor side
        $statusesQuery = HiringOrganizationComplianceV2::getRequirementStatuses($hiringOrg);

        // Submitting on one requirement
        if ($positionType == 'employee') {
            $requirementHistoryRoleId = $contractorEmployeeRole->id;
        } else {
            $requirementHistoryRoleId = $contractorOwnerRole->id;
        }
        $requirementHistory = factory(RequirementHistory::class)->create([
            'requirement_id' => $requirements->first(),
            'contractor_id' => $contractor->id,
            'role_id' => $requirementHistoryRoleId,
            'resource_id' => isset($resource) ? $resource->id : null,
            "completion_date" => Carbon::now(),
        ]);

        $this->assertNotCount(0, $statusesQuery->get(), "Expecting hiring org to have more than 1 requirement assigned out");

        $statusesQueryAsTbl = DB::table(DB::raw("({$statusesQuery->toSql()}) as tbl"));
        $statusesQueryAsTbl
            ->where('contractor_id', DB::raw($contractor->id));

        $statuses = $statusesQueryAsTbl->get();

        $this->assertCount(2, $statuses, "Expecting 2 statuses to be assigned to contractor");

        // Checking to see if its marked as completed

        $completedStatusesCount = $statusesQueryAsTbl
            ->select([
                DB::raw("COUNT(is_active_and_completed) as completed"),
            ])
            ->get()
            ->first()
            ->completed;

        if ($autoApproved) {
            $this->assertEquals(1, $completedStatusesCount, "Expected 1 completed requirement for auto approved");
        } else {
            // If not auto approved, then requires an approved review
            $this->assertEquals(0, $completedStatusesCount, "Expected 0 completed requirements for not auto approved");

            // NOTE: No factory, just creating it manually
            $requirementHistoryReview = RequirementHistoryReview::create([
                'requirement_history_id' => $requirementHistory->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'approver_id' => static::$hiringOrgOwnerRole->id,
                'status' => 'approved',
                "status_at" => Carbon::now(),
                "notes" => "Testing an approved requirement",
            ]);

            $completedStatusesCountWithManualApproval = $statusesQueryAsTbl
                ->select([
                    DB::raw("COUNT(is_active_and_completed) as completed"),
                ])
                ->get()
                ->first()
                ->completed;

            $this->assertEquals(1, $completedStatusesCountWithManualApproval, "Expected 1 completed requirement after approving requirement");
        }
    }

    /**
     * @group HiringOrganizationCompliance
     * @testdox Requirement Compliance: $requirementType, $positionType, auto approved: $autoApproved
     */
    public function testGetRequirementStatusesLive()
    {
        $query = HiringOrganizationComplianceV2::getRequirementStatuses(HiringOrganization::find(114));

        $res = $query->get();

        $this->assertNotNull($res);
    }

    public function hiringOrgContractorComplianceLevels()
    {
        $septodontId = 85;
        $entegrus = 34;
        $sealedAir = 100;
        $watay = 75;

        return [
            [
                "hiringOrgId" => $septodontId,
                "contractorId" => 6141,
                "corporate" => 100,
            ],
        ];
    }

    /**
     * @group HiringOrganizationCompliance
     * @dataProvider hiringOrgContractorComplianceLevels
     *
     * @return void
     */
    public function testHiringOrgDashboardComplianceLevels($hiringOrgId, $contractorId, $corporate = null, $employee = null)
    {
        $hiringOrg = HiringOrganization::find($hiringOrgId);
        $hiringOrgOwnerRole = $hiringOrg->owner;

        try {
            // Building Query
            $query = HiringOrganizationComplianceV2::getContractorsWithComplianceQuery($hiringOrg);
            $complianceQuery = DB::table(DB::raw("({$query->toSql()}) as " . __FUNCTION__));
            $complianceQuery->where("contractor_id", DB::raw($contractorId));

            $this->assertCount(1, $complianceQuery->get(), "Expecting only 1 record for the contractor $contractorId from hiring org $hiringOrgId");

            $results = $complianceQuery->first();

            if (isset($corporate)) {
                $this->assertEquals($corporate, $results->contractor_compliance, "Incorrect Corporate Compliance Level");
            }
            if (isset($employee)) {
                $this->assertEquals($employee, $results->employee_compliance, "Incorrect Employee Compliance Level");
            }

        } catch (Exception $e) {

            Log::debug(__METHOD__, [
                'message' => $e->getMessage(),
            ]);

            $sqlFileContents = "\n\n-- " . __FUNCTION__ . " - Contractor $contractorId \n\n" . $complianceQuery->toSql() . ";";
            Storage::disk('scripts')->put(__FUNCTION__ . ".sql", $sqlFileContents);

            $getRequirementStatusesQuery = HiringOrganizationComplianceV2::getRequirementStatuses($hiringOrg);
            $getRequirementStatusesQueryFiltered = DB::table(DB::raw("({$getRequirementStatusesQuery->toSql()}) as getRequirementStatusesQuery"))
                ->where('contractor_id', DB::raw($contractorId));
            Storage::disk('scripts')->put(__FUNCTION__ . "-getRequirementStatuses.sql", $getRequirementStatusesQueryFiltered->toSql());

            throw $e;
        }
    }

    /**
     * @testdox Employees that have not accepted the invite should not affect the compliance levels
     *
     * @group HiringOrganizationCompliance
     * @group Debug
     */
    public function testPendingEmployeeRequirements()
    {
        // NOTE: Creating hiring org and contractor again for fresh start as counts are specific
        $hiringOrg = factory(HiringOrganization::class)->create([
            'name' => __FUNCTION__,
        ]);
        $contractor = factory(Contractor::class)->create([
            'name' => __FUNCTION__,
        ]);
        $hiringOrg->contractors()->attach($contractor);

        $badEmployeeUser = factory(User::class)->create([
            'first_name' => __FUNCTION__,
            'last_name' => "BAD",
            'password' => null,
        ]);
        /** Not authenticated */
        $badEmployeeRole = factory(Role::class)->create([
            'role' => 'employee',
            'entity_key' => "contractor",
            "entity_id" => $contractor->id,
            "user_id" => $badEmployeeUser->id,
        ]);
        $badEmployeeUser->update([
            'current_role_id' => $badEmployeeRole->id,
        ]);

        $goodEmployeeUser = factory(User::class)->create([
            'first_name' => __FUNCTION__,
            'last_name' => "GOOD",
        ]);
        /** Authenticated Employee*/
        $goodEmployeeRole = factory(Role::class)->create([
            'role' => 'employee',
            'entity_key' => "contractor",
            "entity_id" => $contractor->id,
            "user_id" => $goodEmployeeUser->id,
        ]);
        $goodEmployeeUser->update([
            'current_role_id' => $goodEmployeeRole->id,
        ]);

        // Validating accounts were created correctly
        $this->assertFalse($badEmployeeUser->getHasPasswordAttribute(), "Should not have password");
        $this->assertTrue($goodEmployeeUser->getHasPasswordAttribute(), "Should have password");

        // Creating positions and requirements
        $position = factory(Position::class)->create([
            'hiring_organization_id' => $hiringOrg->id,
            'position_type' => 'employee',
            'is_active' => true
        ]);
        $requirement = factory(Requirement::class)->create([
            'hiring_organization_id' => $hiringOrg->id,
            'type' => 'upload',
        ]);
        $requirementContent = factory(RequirementContent::class)->create([
            'name' => __FUNCTION__,
            'requirement_id' => $requirement->id,
        ]);
        $position->requirements()->attach($requirement);

        // Assigning position to contractor
        $contractor->positions()->attach($position);
        $badEmployeeRole->positions()->attach($position);
        $goodEmployeeRole->positions()->attach($position);

        $contractorsComplianceQueryRaw = HiringOrganizationComplianceV2::getContractorsWithComplianceQuery($hiringOrg);
        $contractorsComplianceQuery = DB::table(DB::raw("({$contractorsComplianceQueryRaw->toSql()}) as tbl"))
            ->where('contractor_id', DB::raw($contractor->id));

        $contractorsComplianceRes = $contractorsComplianceQuery->get();
        $this->assertCount(1, $contractorsComplianceRes, "Should have 1 requirement");

        $contractorWithCompliance = $contractorsComplianceRes->first();
        $this->assertEquals(1, $contractorWithCompliance->employee_requirement_count);
    }
}
