<?php

/**
 * This script creates example accounts to be used for testing / demo purposes
 */

use App\Models\Contractor;
use App\Models\Facility;
use App\Models\HiringOrganization;
use App\Models\Position;
use App\Models\Requirement;
use App\Models\RequirementContent;
use App\Models\RequirementHistory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExampleAccountSeeder extends Seeder
{
    private $contractorCount = 5;
    private $requirementCount = 20;
    private $positionCount = 2;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = new Faker\Generator();

        try {
            DB::beginTransaction();
            $adminEmail = 'admin@contractorcompliance.io';

            // Create Example Hiring Organization
            $hiringOrg = $this->createHiringOrg();

            // Create example contractor
            $contractors = $this->createContractors();

            // Pairing hiring org and contractor
            foreach ($contractors as $contractor) {
                $this->pairHiringOrgAndContractor($hiringOrg, $contractor);

                $positions = $hiringOrg->positions;
                foreach ($positions as $position) {
                    // Adding random factor to positions
                    // NOTE: $faker->boolean doesn't work here for some reason
                    // Resorted to rand()
                    $toAssignPosition = rand(0, 1) == 1;
                    if ($toAssignPosition) {
                        $this->assignPositionToContractor($position, $contractor);
                    }
                }
            }

            // Create admin account
            $admin = $this->createAdminUser($adminEmail);

            // Have contractor complete random number of requirements
            foreach ($contractors as $contractor) {
                $this->contractorCompleteRequirements($contractor);
            }

            DB::commit();
        } catch (Exception $e) {

            throw $e;

            DB::rollback();
        }

    }

    // ===== User Methods ===== //

    protected function createAdminUser($adminEmail)
    {
        // Check to see if admin exists
        $admin = User::where('email', $adminEmail)->first();

        $adminAccountDetails = [
            'email' => $adminEmail,
            'password' => bcrypt('password'),
            "first_name" => 'Admin',
            "last_name" => "Admin",
            'global_admin' => 1,
        ];

        // If admin account doesn't exist, create the account
        if ($admin == null) {
            // Create account
            $admin = factory(User::class)->create($adminAccountDetails);
        } else {
            // Else update with creds
            $admin->update($adminAccountDetails);
        }
    }

    // =============== Hiring Organization Methods =============== //

    // Creates a hiring organization
    protected function createHiringOrg()
    {
        // Get first hiring org, or create a new one
        $hiringOrg = HiringOrganization::first();
        if (!isset($hiringOrg)) {
            $hiringOrg = factory(HiringOrganization::class)->create([
                'name' => "Example Hiring Organization",
            ]);
        } else {
            $hiringOrg->update([
                'name' => "Example Hiring Organization",
            ]);
        }

        $hiringOrgOwner = $this->createHiringOrgOwner($hiringOrg);

        // Creating Facilities
        $facilities = factory(Facility::class, 1)->create([
            'hiring_organization_id' => $hiringOrg['id'],
        ]);

        // Connecting Positions to Facilities
        foreach ($facilities as $facility) {

            // Creating 2 Positions per facility
            $positions = factory(Position::class, $this->positionCount)->create([
                'hiring_organization_id' => $hiringOrg['id'],
                'position_type' => 'contractor',
            ]);

            foreach ($positions as $position) {
                DB::table('facility_position')->insert(array(
                    array(
                        'facility_id' => $facility['id'],
                        'position_id' => $position['id'],
                    ),
                ));
            }
        }

        // Creating requirements for hiring org
        $requirements = $this->createRequirements($hiringOrg);

        // Connecting requirements to positions
        foreach ($requirements as $requirement) {
            foreach ($positions as $position) {
                $this->pairRequirementAndPosition($requirement, $position);
            }
        }

        return $hiringOrg;
    }

    protected function createHiringOrgOwner($hiringOrg)
    {

        $hiringOrgOwnerDetails = [
            'email' => "hiring_organization@example.com",
            'password' => bcrypt('password'),
            "first_name" => $hiringOrg['name'],
            "last_name" => "Owner",
        ];

        $owner = User::where('email', $hiringOrgOwnerDetails['email'])
            ->first();

        // If owner is not set, create
        if (!isset($owner)) {
            $owner = factory(User::class)->create($hiringOrgOwnerDetails);
        } else {
            $owner->update($hiringOrgOwnerDetails);
        }

        // Creating Owner
        Role::create([
            "user_id" => $owner['id'],
            "entity_key" => "hiring_organization",
            "entity_id" => $hiringOrg['id'],
            "role" => "owner",
        ]);

        return $owner;
    }

    // =============== Contractor Methods =============== //

    protected function createContractors()
    {
        $defaultContractorUser = User::where('email', 'contractor@example.com')->first();

        if (!isset($defaultContractorUser)) {
            // Creating default contractor
            $defaultContractorUser = factory(User::class)->create([
                'email' => 'contractor@example.com',
                'password' => bcrypt('password'),
            ]);
        } else {
            $defaultContractorUser->update([
                'password' => bcrypt('password'),
            ]);
        }
        $defaultContractor = factory(Contractor::class)->create([
            'owner_id' => $defaultContractorUser['id'],
        ]);
        Role::create([
            "user_id" => $defaultContractorUser['id'],
            "entity_key" => "contractor",
            "entity_id" => $defaultContractor['id'],
            "role" => "owner",
        ]);

        $contractorUsers = factory(User::class, $this->contractorCount)->create();

        foreach ($contractorUsers as $contractorUser) {
            $contractor = factory(Contractor::class)->create([
                'owner_id' => $contractorUser['id'],
            ]);

            // Creating Owner
            Role::create([
                "user_id" => $contractorUser['id'],
                "entity_key" => "contractor",
                "entity_id" => $contractor['id'],
                "role" => "owner",
            ]);
        }
        $contractors = Contractor::get();
        return $contractors;
    }

    // =============== Requirement Methods =============== //

    protected function createRequirements($hiringOrg)
    {
		$lastRequirementId = Requirement::max('id');
		if(is_null($lastRequirementId)){
			Log::info("Could not find last requirement ID. Setting to 0");
			$lastRequirementId = 0;
		}

		// Creating random requirements
        $requirements = factory(Requirement::class, $this->requirementCount)->create([
            'hiring_organization_id' => $hiringOrg['id'],
		]);
		// For each requirement, create content
        foreach ($requirements as $requirement) {
            factory(RequirementContent::class)->create([
                'requirement_id' => $requirement['id'],
            ]);
        };

		// Creating specific set of requirements
        $requirementTypes = with(new Requirement)->getTypes();

        foreach ($requirementTypes as $type) {
            foreach ([true, false] as $countIfNotApproved) {
                $typedRequirement = factory(Requirement::class)->create([
                    'hiring_organization_id' => $hiringOrg['id'],
					'type' => $type,
					'count_if_not_approved' => $countIfNotApproved
				]);
				factory(RequirementContent::class)->create([
					'requirement_id' => $typedRequirement['id'],
					'lang' => 'en',
					'name' => "Generated $type - $countIfNotApproved",
					'description' => "Generated requirement of type $type. Count if not approved set to $countIfNotApproved"
				]);
            }
		}

		// Returning only new requirements
		$requirements = Requirement::where('id', '>', $lastRequirementId)->get();
		if(is_null($requirements) || sizeof($requirements) == 0){
			Log::debug($requirements);
			// throw new Exception("Requirements is null, or none were present");
			$requirements = Requirement::get();
		}

        return $requirements;
    }

    protected function pairRequirementAndPosition($requirement, $position)
    {
        $requirement->positions()->sync($position->id, false);
    }

    protected function pairHiringOrgAndContractor($hiringOrg, $contractor)
    {
        // Connect hiring org to contractor
        $hiringOrg->contractors()->sync($contractor->id, false);
    }

    protected function assignPositionToContractor($position, $contractor)
    {
        $contractor->positions()->attach($position->id);
    }

    protected function contractorCompleteRequirements($contractor)
    {
        try {

            $faker = Faker\Factory::create();
            // Find requirements contractor needs to complete

            $pendingRequirements = $contractor->requirements;

            // Error handling
            if (is_null($pendingRequirements)) {
                throw new Exception("Requirements was null");
            }
            if (sizeof($pendingRequirements) == 0) {
				Log::warning("There were 0 requirements to be completed.");
            }

            foreach ($pendingRequirements as $pendingRequirement) {
                $requirement = Requirement::where('id', $pendingRequirement->requirement_id)->first();

                // TODO: Add in randomizer to determine if we need to complete the requirement
                $toCompleteRequirement = rand(0, 1) == 1;

                if ($toCompleteRequirement) {
                    // Complete Requirement
                    $this->completeRequirement($requirement, $contractor->roles[0], $contractor);
                }
            }
        } catch (Exception $e) {
            Log::error("Failed to generate completed requirements for contractor: " . $e->getMessage());
        }
    }

    protected function completeRequirement($requirement, $role, $contractor = null)
    {
        if (is_null($requirement)) {
            throw new Exception("Requirement was null - can't mark it as completed");
        }

        $requirementId = $requirement->id;
        if (is_null($requirementId)) {
            Log::info("Requirement");
            Log::info($requirement);
            throw new Exception("Requirement ID was null - can't mark it as completed");
        }

        $contractorId = $contractor->id;

        $requirementHistory = RequirementHistory::create([
            "requirement_id" => $requirementId,
            "completion_date" => now(),
            "role_id" => $role->id,
			"contractor_id" => $contractorId,
			"certificate_file" => "SAMPLEFILE"
        ]);

        return $requirementHistory;
    }

}
