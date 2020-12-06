<?php

use App\Models\Facility;
use App\Models\Position;
use Illuminate\Database\Seeder;

class HiringOrganizationSeeder extends Seeder
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

            $org = factory(App\Models\HiringOrganization::class)->create();

            $owner = factory(App\Models\User::class)->create([
                'password' => bcrypt('password'),
                "first_name" => $org['name'],
                "last_name" => "Owner",
            ]);

            // Creating Owner
            \App\Models\Role::create([
                "user_id" => $owner['id'],
                "entity_key" => "hiring_organization",
                "entity_id" => $org['id'],
                "role" => "owner",
            ]);

            // Creating Facilities
            $facilities = factory(Facility::class, 1)->create([
                'hiring_organization_id' => $org['id'],
            ]);

            // Connecting Positions to Facilities
            foreach($facilities as $facility){

                // Creating 2 Positions per facility
                $positions = factory(Position::class, 2)->create([
                    'hiring_organization_id' => $org['id'],
                    'position_type' => 'contractor',
                ]);

                foreach($positions as $position){
                    DB::table('facility_position')->insert(array(
                        array(
                            'facility_id' => $facility['id'],
                            'position_id' => $position['id'],
                        ),
                    ));
                }
            }


            DB::commit();

        } catch (Exception $e) {
            throw $e;
            DB::rollback();
        }
    }
}
