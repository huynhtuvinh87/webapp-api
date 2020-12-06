<?php

namespace Tests\Feature;

use App\Lib\Services\HiringOrganizationComplianceV2;
use App\Models\Contractor;
use App\Models\HiringOrganization;
use App\Models\Position;
use App\Models\Requirement;
use App\Models\RequirementContent;
use App\Models\RequirementHistory;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HOCV2GetRequirementStatusesTest extends TestCase
{
	use DatabaseTransactions;

	protected static $isInit = false;

	protected static $hiringOrg = null;
	protected static $hiringOrgOwnerRole = null;
	protected static $hiringOrgOwnerUser = null;

	protected static $contractor = null;
	protected static $contractorOwnerRole = null;
	protected static $contractorOwnerUser = null;
	protected static $contractorEmployeeUser = null;
	protected static $contractorEmployeeRole = null;

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

			// Creating Contractor
			static::$contractor = factory(Contractor::class)->create([
				'name' => "Test Contractor",
			]);
			// Creating Contractor User
			static::$contractorOwnerUser = factory(User::class)->create([
				"first_name" => static::$hiringOrg->name,
				"last_name" => "Owner",
			]);
			// Creating Contractor Owner Role
			static::$contractorOwnerRole = factory(Role::class)->create([
				"role" => "owner",
				"user_id" => static::$contractorOwnerUser->id,
				"entity_key" => "contractor",
				"entity_id" => static::$contractor->id,
			]);
			static::$contractorOwnerUser->update([
				'current_role_id' => static::$contractorOwnerRole->id,
			]);

			// Creating Contractor employee
			static::$contractorEmployeeUser = factory(User::class)->create([
				"first_name" => static::$hiringOrg->name,
				"last_name" => "Employee",
			]);
			static::$contractorEmployeeRole = factory(Role::class)->create([
				"role" => "employee",
				"user_id" => static::$contractorEmployeeUser->id,
				"entity_key" => "contractor",
				"entity_id" => static::$contractor->id,
			]);

			/* ----------------- Facilities, Positions, and Requirements ---------------- */

			// ----- Configuration Params ---- //

			$positionTypes = collect([
				'contractor',
				'employee',
				'resource',
			]);

			$requirementTypes = collect([
				'review',
				'upload',
				'upload_date',
			]);

			$isPositionActiveOpts = collect([true, false]);

			$configs = $positionTypes->each(function ($positionType) use ($isPositionActiveOpts, $requirementTypes) {
				$isPositionActiveOpts->each(function ($isPositionActive) use ($positionType, $requirementTypes) {

					// Creating Position
					$position = factory(Position::class)->create([
						'hiring_organization_id' => static::$hiringOrg->id,
						'is_active' => $isPositionActive,
						'position_type' => $positionType,
						'name' => ($isPositionActive ? "Active" : "Inactive") . " " . $positionType,
					]);

					// Creating Requirements

					// Requirement Types
					$requirementTypes->each(function ($requirementType) use ($position) {
						// Requirement Auto Complete

						collect([true, false])->each(function ($autoComplete) use ($requirementType, $position) {

							// Requirement is actioned
							collect([true, false])->each(function ($isActioned) use ($autoComplete, $requirementType, $position) {

								$requirement = factory(Requirement::class)->create([
									'hiring_organization_id' => static::$hiringOrg->id,
									'warning_period' => 7,
									'renewal_period' => 12,
									'type' => $requirementType,
									'count_if_not_approved' => $autoComplete,
									'content_type' => 'text',
								]);

								$requirementName = "Test Requirement " . $requirement->id . ": " . $requirementType;
								$requirementName .= $autoComplete ? " Auto Approved" : "";
								$requirementName .= $isActioned ? " Actioned" : "";

								factory(RequirementContent::class)->create([
									"requirement_id" => $requirement->id,
									'lang' => 'en',
									'name' => $requirementName,
									"text" => null,
								]);

								// Assigning requirements to position
								$position->requirements()->attach($requirement);

								if($isActioned){
									factory(RequirementHistory::class)->create([
										'contractor_id' => static::$contractor->id,
										'requirement_id' => $requirement->id,
										'completion_date' => Carbon::now(),
										'role_id' => $position->position_type == 'contractor' ? static::$contractorOwnerRole->id : static::$contractorEmployeeRole->id
									]);
								}
							});

						});
					});

					// Assigning position to contractor

					if($position->position_type == 'employee'){
						static::$contractorEmployeeRole->positions()->attach($position);
					} else if ($position->position_type == 'contractor'){
						static::$contractor->positions()->attach($position);
					}
				});
			});

			/* ---------------------------------- Misc ---------------------------------- */

			// Connecting Hiring Org with Contractor
			static::$hiringOrg->contractors()->sync(static::$contractor, [
				'accepted' => true,
			]);

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

		// Setup of Facilities / Positions / Requirements
		$this->assertCount(12, static::$contractor->requirements);
	}

	/* ---------------------------- Helper Functions ---------------------------- */

	/* ---------------------------------- Tests --------------------------------- */

	/**
	 * @group HiringOrganizationCompliance
	 * @testdox Testing the use case of corporate requirements with no role ID - should be able to handle
	 */
	public function testGetRequirementStatusCorporateNoRole()
	{
		// NOTE: For some reason, static::$hiringOrg can't be used. FK check fails.
		$hiringOrg = factory(HiringOrganization::class)->create();
		$contractor = factory(Contractor::class)->create();
		$hiringOrg->contractors()->attach($contractor);

		if(!isset($hiringOrg)){
			throw new Exception("Hiring Org was not defined");
		}
		if(!isset($contractor)){
			throw new Exception("Contractor was not defined");
		}

		// Create requirement
		$requirement = factory(Requirement::class)->create([
			"hiring_organization_id" => $hiringOrg->id,
			"count_if_not_approved" => true,
			"type" => "upload"
		]);
		$requirementContent = factory(RequirementContent::class)->create([
			"name" => __METHOD__,
			"requirement_id" => $requirement->id,
		]);

		//Create Position
		$position = factory(Position::class)->create([
			"hiring_organization_id" => $hiringOrg->id,
			"name" => __METHOD__,
			"is_active" => true,
			"position_type" => "contractor"
		]);
		$position->requirements()->save($requirement);

		if(!isset($requirement)){
			throw new Exception("Requirement not found");
		}

		//Connecting Position to Contractor
		$contractor->positions()->save($position);

		// Create Submission - No role id

		$requirementHistory = factory(RequirementHistory::class)->create([
			"contractor_id" => $contractor->id,
			"requirement_id" => $requirement->id,
			"completion_date" => Carbon::now()
		]);
		// Ensuring the record was created
		if(!isset($requirementHistory)){
			throw new Exception("Requirement history was not created");
		}

		// ---- Test ----- //

		$reqStatusesQuery = HiringOrganizationComplianceV2::getRequirementStatuses($hiringOrg);
		$statusQuery = DB::table(DB::raw("({$reqStatusesQuery->toSql()}) as tbl"))
			->where('contractor_id', DB::raw($contractor->id));

		$this->assertCount(1, $statusQuery->get(), "Contractor should have 1 requirement");

		// Testing completed Requirements
		$completedStatusQuery = $statusQuery
			->where('is_completed', DB::raw(true));
		$this->assertCount(1, $completedStatusQuery->get(), "Should have 1 completed requirement" );


	}
}
