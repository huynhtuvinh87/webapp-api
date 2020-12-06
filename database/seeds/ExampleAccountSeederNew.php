<?php

use App\Models\HiringOrganization;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExampleAccountSeederNew extends Seeder
{
    public $user = null;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create user for accounts to be attached to
        $this->user = $this->createUser();

        // Create hiring org & attach user
        $hiringOrg = $this->createHiringOrganization($this->user);

        // Create contractors & attach user
        $contractors = $this->createContractors($this->user, 1000);
    }

    /* ---------------------------- Creation Methods ---------------------------- */

    public function createUser()
    {
        // TODO: Disconnect pre-existing roles

        $this->user = User::updateOrCreate([
            "email" => "admin@example.com",
        ], [
            'password' => bcrypt('password'),
            "first_name" => "Testing",
            "last_name" => "Account",
        ]);

        return $this->user;
    }

    public function createHiringOrganization(User $ownerUser)
    {
        $hiringOrg = factory(HiringOrganization::class)
            ->create();

        $ownerRole = factory(Role::class)
            ->create([
                'user_id' => $ownerUser->id,
                'role' => 'owner',
                'entity_key' => 'hiring_organization',
                'entity_id' => $hiringOrg->id,
            ]);

        $ownerUser->update([
            'current_role_id' => $ownerRole->id,
        ]);

        $this->createPositions($hiringOrg);

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
                return $position;
            })
            ->toArray();

        $positions = DB::table('positions')
            ->insert($positionsInsertData);

        return $positions;
    }

    public function createContractors(User $ownerUser, $count)
    {
        $initialContractorName = "Contractor #";
        $contractorInsertData = [];
        for ($i = 0; $i < $count; $i++) {
            $contractorInsertData[] = [
                "name" => $initialContractorName,
                "owner_id" => $ownerUser->id,
                "is_active" => 1,
                "stripe_id" => "cus_asdasd",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now(),
            ];
        }

        DB::table('contractors')
            ->insert($contractorInsertData);

        // Adding ID to name for easier finding
        DB::update(DB::raw("UPDATE contractors SET name = CONCAT(name, id) WHERE name = '$initialContractorName'"));
    }

}
