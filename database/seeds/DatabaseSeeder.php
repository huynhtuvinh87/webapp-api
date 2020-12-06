<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * PRODUCTION DATA SEEDER
     * @return void
     */
    public function run()
    {
        //$this->call(WorkTypesTableSeeder::class);
        $this->call(CountriesTableSeeder::class);
        $this->call(StatesTableSeeder::class);
        $this->call(TaxTableSeeder::class);
        $this->call(TaskTypeSeeder::class);
    }
}
