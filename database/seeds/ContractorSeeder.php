<?php

use Illuminate\Database\Seeder;

class ContractorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            DB::beginTransaction();

            $org = factory(App\Models\Contractor::class)->create();

            $owner = factory(App\Models\User::class)->create([
                'password' => bcrypt('password'),
                "first_name" => $org['name'],
                "last_name" => "Owner",
            ]);

            \App\Models\Role::create([
                "user_id" => $owner['id'],
                "entity_key" => "contractor",
                "entity_id" => $org['id'],
                "role" => "owner",
            ]);

            DB::commit();

        } catch (Exception $e) {
            throw $e;
            DB::rollback();
        }
    }
}
