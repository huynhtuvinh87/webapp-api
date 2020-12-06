<?php

use Illuminate\Database\Seeder;

class TestDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * TESTING SEEDERS
     * @return void
     */
    public function run()
    {
        $this->call(TestUserSeeder::class);
    }
}
