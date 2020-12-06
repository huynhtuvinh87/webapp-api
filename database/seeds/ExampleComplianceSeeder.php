<?php

use App\Models\Contractor;
use App\Models\HiringOrganization;
use App\Models\Position;
use App\Models\Requirement;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ExampleComplianceSeeder extends Seeder
{
    public $hiringOrgOwnerEmail = "owner@hiring-organization.com";
    public $contractorOwnerEmail = "owner@contractor.com";
    public $contractorEmployeeEmail = "employee@contractor.com";
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create user for accounts to be attached to
        $hiringOrgOwnerUser = $this->createUser($this->hiringOrgOwnerEmail);
        $contractorOwnerUser = $this->createUser($this->contractorOwnerEmail);
        $contractorEmployeeUser = $this->createUser($this->contractorEmployeeEmail);


        // Create hiring org & attach user
        $hiringOrg = $this->createHiringOrganization($hiringOrgOwnerUser);


        // Create Contractor
        $contractor = Contractor::updateOrCreate([
            'name' => "CONTRACTOR: " . "ExampleComplianceSeeder",
        ], [
            'owner_id' => 123
        ]);
        $subscription = Subscription::updateOrCreate([
            "stripe_id" => "sub_ExampleComplianceSeeder"
        ], [
            "contractor_id" => $contractor->id,
            "name" => "default",
            "stripe_plan" => "small",
            "quantity" => 1,
            "created_at" =>Carbon::now(),
            "updated_at" =>Carbon::now(),
        ]);
        $contractorOwnerRole = Role::updateOrCreate([
            'entity_key' => 'contractor',
            'entity_id' => $contractor->id,
            'role' => 'owner',
        ], [
            'user_id' => $contractorOwnerUser->id
        ]);
        $contractor->update([
            'owner_id' => $contractorOwnerRole->id
        ]);

        // Attaching hiring org and contractor
        $hiringOrg->contractors()->sync($contractor, ['accepted' => true]);
        $hiringOrgPositions = $hiringOrg->positions;
        $contractor->positions()->attach($hiringOrgPositions);

    }

    /* ---------------------------- Creation Methods ---------------------------- */

    public function createUser($email)
    {
        // TODO: Disconnect pre-existing roles

        $this->user = User::updateOrCreate([
            "email" => $email,
        ], [
            'password' => bcrypt('password'),
            "first_name" => "Testing",
            "last_name" => "Account",
        ]);

        return $this->user;
    }

    public function createHiringOrganization(User $ownerUser)
    {
        //  $hiringOrg = factory(HiringOrganization::class)
        //      ->create([
        //          "name" => "HO: " . __METHOD__
        //      ]);

        $hiringOrg = HiringOrganization::updateOrCreate([
            "name" => "HO: " . "ExampleComplianceSeeder",
        ]);

        $ownerRole = Role::updateOrCreate([
            'role' => 'owner',
            'entity_key' => 'hiring_organization',
            'entity_id' => $hiringOrg->id,
        ], [
            'user_id' => $ownerUser->id,
        ]);

        $ownerUser->update([
            'current_role_id' => $ownerRole->id,
        ]);

        // Cleaning positions
        DB::table("positions")
            ->where('hiring_organization_id', $hiringOrg->id)
            ->delete();

        $positions = $this->createPositions($hiringOrg);

        // Clean requirements
        DB::table("requirements")
            ->where('hiring_organization_id', $hiringOrg->id)
            ->delete();

        $requirements = $this->createRequirements($hiringOrg);

        // Assigning requirements to positions
        $positions->each(function ($position) use ($requirements) {
            $position->requirements()->sync($requirements);
        });

        // Assigning positions to contractor

        return $hiringOrg;
    }

    public function createPositions(HiringOrganization $hiringOrg)
    {
        $positionTypes = [
            'contractor',
            'employee',
            'resource',
        ];

        $positionsInsertData = collect($positionTypes)
            ->map(function ($positionType) use ($hiringOrg) {
                $position = factory(Position::class)->make();
                $position['position_type'] = $positionType;
                $position['hiring_organization_id'] = $hiringOrg->id;
                $position['is_active'] = true;
                $position['created_at'] = Carbon::now();
                $position['updated_at'] = Carbon::now();
                return $position;
            })
            ->toArray();

        DB::table('positions')
            ->insert($positionsInsertData);

        $positions = Position
            ::where("hiring_organization_id", $hiringOrg->id);

        return $positions;
    }

    public function createRequirements(HiringOrganization $hiringOrg)
    {
        $requirementTypes = [
            "upload",
            "review",
        ];
        $autoApproveStates = [
            true,
            false,
        ];
        $autoApproveStates = [
            true,
            false,
        ];

        $insertData = collect($requirementTypes)
            ->flatMap(function ($requirementType) use ($hiringOrg, $autoApproveStates) {
                return collect($autoApproveStates)
                    ->map(function ($autoApproved) use ($hiringOrg, $requirementType) {
                        $requirement = factory(Requirement::class)->make();
                        $requirement['type'] = $requirementType;
                        $requirement['hiring_organization_id'] = $hiringOrg->id;
                        $requirement['created_at'] = Carbon::now();
                        $requirement['updated_at'] = Carbon::now();
                        $requirement['renewal_period'] = 12;
                        $requirement['warning_period'] = 12;
                        $requirement['content_type'] = 'none';
                        $requirement['count_if_not_approved'] = $autoApproved;
                        return $requirement;
                    });
            })
            ->toArray();

        DB::table('requirements')
            ->insert($insertData);

        // Creating requirement content for each one
        $requirements = Requirement
            ::where('hiring_organization_id', $hiringOrg->id)
            ->get();

        $requirementContentInsertData = $requirements
            ->map(function ($requirement) {
                $autoApproveText = $requirement->count_if_not_approved ? 'auto approved' : 'manual approved';
                $requirementContent = [
                    'name' => "$requirement->type - $autoApproveText",
                    "description" => "Requirement Type:\t$requirement->type\nAuto Approved:\t\t$autoApproveText\nRenewal Period:\t\t$requirement->renewal_period",
                    "requirement_id" => $requirement->id,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now(),
                ];

                return $requirementContent;
            })
            ->toArray();

        DB::table('requirement_contents')
            ->insert($requirementContentInsertData);

        return $requirements;
    }

}
