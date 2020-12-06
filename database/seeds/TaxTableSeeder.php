<?php

use Illuminate\Database\Seeder;

class TaxTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('tax')->insert(array (
            0 => 
            array (
                'id' => 1,
                'province_name' => 'Alberta',
                'state_id' => 663,
                'country_id' => 38,
                'abbreviation' => 'AB',
                'tax_rate' => 5,
                'tax_type' => 'GST',
            ),
            1 => 
            array (
                'id' => 2,
                'province_name' => 'British Columbia',
                'state_id' => 664,
                'country_id' => 38,
                'abbreviation' => 'BC',
                'tax_rate' => 12,
                'tax_type' => 'GST+PST',
            ),
            2 => 
            array (
                'id' => 3,
                'province_name' => 'Manitoba',
                'state_id' => 665,
                'country_id' => 38,
                'abbreviation' => 'MB',
                'tax_rate' => 13,
                'tax_type' => 'GST+PST',
            ),
            3 => 
            array (
                'id' => 4,
                'province_name' => 'New Brunswick',
                'state_id' => 666,
                'country_id' => 38,
                'abbreviation' => 'NB',
                'tax_rate' => 15,
                'tax_type' => 'HST',
            ),
            4 => 
            array (
                'id' => 5,
                'province_name' => 'Newfoundland and Labrador',
                'state_id' => 667,
                'country_id' => 38,
                'abbreviation' => 'NL',
                'tax_rate' => 15,
                'tax_type' => 'HST',
            ),
            5 => 
            array (
                'id' => 6,
                'province_name' => 'Northwest Territories',
                'state_id' => 668,
                'country_id' => 38,
                'abbreviation' => 'NT',
                'tax_rate' => 5,
                'tax_type' => 'GST',
            ),
            6 => 
            array (
                'id' => 7,
                'province_name' => 'Nova Scotia',
                'state_id' => 669,
                'country_id' => 38,
                'abbreviation' => 'NS',
                'tax_rate' => 15,
                'tax_type' => 'HST',
            ),
            7 => 
            array (
                'id' => 8,
                'province_name' => 'Nunavut',
                'state_id' => 670,
                'country_id' => 38,
                'abbreviation' => 'NU',
                'tax_rate' => 5,
                'tax_type' => 'GST',
            ),
            8 => 
            array (
                'id' => 9,
                'province_name' => 'Ontario',
                'state_id' => 671,
                'country_id' => 38,
                'abbreviation' => 'ON',
                'tax_rate' => 13,
                'tax_type' => 'HST',
            ),
            9 => 
            array (
                'id' => 10,
                'province_name' => 'Prince Edward Island',
                'state_id' => 672,
                'country_id' => 38,
                'abbreviation' => 'PE',
                'tax_rate' => 15,
                'tax_type' => 'HST',
            ),
            10 => 
            array (
                'id' => 11,
                'province_name' => 'Quebec',
                'state_id' => 673,
                'country_id' => 38,
                'abbreviation' => 'QC',
                'tax_rate' => 14,
                'tax_type' => 'GST+PST',
            ),
            11 => 
            array (
                'id' => 12,
                'province_name' => 'Saskatchewan',
                'state_id' => 674,
                'country_id' => 38,
                'abbreviation' => 'SK',
                'tax_rate' => 11,
                'tax_type' => 'GST+PST',
            ),
            12 => 
            array (
                'id' => 13,
                'province_name' => 'Yukon',
                'state_id' => 675,
                'country_id' => 38,
                'abbreviation' => 'YT',
                'tax_rate' => 5,
                'tax_type' => 'GST',
            ),
        ));
        
        
    }
}
