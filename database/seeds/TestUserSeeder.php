<?php

use App\Models\User;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
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

            $user1 = User::where('email', 'slessard@contractorcompliance.ca')->first();

            if (!isset($user1)) {
                $user1 = factory(App\Models\User::class)->create([
                    'email' => 'slessard@contractorcompliance.ca',
                    'password' => bcrypt('Password1234'),
                ]);
            }

            $user2 = User::where('email', 'alampert@contractorcompliance.io')->get()->first();

            if (!isset($user2)) {
                $user2 = factory(App\Models\User::class)->create([
                    'email' => 'alampert@contractorcompliance.io',
                    'password' => bcrypt('Password1234'),
                    'first_name' => "Andrew",
                    'last_name' => "Lampert",
                ]);
            }

            $user3 = factory(App\Models\User::class)->create();

            $user4 = factory(App\Models\User::class)->create();

            $user5 = factory(App\Models\User::class)->create();

            $org1 = factory(App\Models\HiringOrganization::class)->create();

            $contractor1 = factory(\App\Models\Contractor::class)->create([
                "owner_id" => $user2['id'],
            ]);

            $contractor2 = factory(\App\Models\Contractor::class)->create([
                "owner_id" => $user4['id'],
            ]);

            $user1Role = \App\Models\Role::create([
                "user_id" => $user1->id,
                "entity_key" => "hiring_organization",
                "entity_id" => $org1['id'],
                "role" => "owner",
            ]);

            \App\Models\Role::create([
                "user_id" => $user2->id,
                "entity_key" => "hiring_organization",
                "entity_id" => $org1['id'],
                "role" => "owner",
            ]);

            \App\Models\Role::create([
                "user_id" => $user2['id'],
                "entity_key" => "contractor",
                "entity_id" => $contractor1['id'],
                "role" => "owner",
            ]);

            \App\Models\Role::create([
                "user_id" => $user3['id'],
                "entity_key" => "contractor",
                "entity_id" => $contractor1['id'],
                "role" => "employee",
            ]);

            \App\Models\Role::create([
                "user_id" => $user4['id'],
                "entity_key" => "contractor",
                "entity_id" => $contractor2['id'],
                "role" => "owner",
            ]);

            \App\Models\Role::create([
                "user_id" => $user5['id'],
                "entity_key" => "contractor",
                "entity_id" => $contractor2['id'],
                "role" => "employee",
            ]);

            \App\Models\Role::create([
                "user_id" => $user5['id'],
                "entity_key" => "contractor",
                "entity_id" => $contractor2['id'],
                "role" => "owner",
            ]);

            $user1->update([
                'current_role_id' => $user1->highestRole->id,
            ]);
            $user1->save();

            DB::commit();
        } catch (Exception $e) {

            throw $e;

            DB::rollback();
        }
    }
}
