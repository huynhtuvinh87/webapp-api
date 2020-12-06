<?php

use Illuminate\Database\Seeder;

class WorkTypesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     * DEPRECATED
     * Use the following command instead: php artisan import:naics
     * @return void
     */
    public function run()
    {

        return;

        \DB::table('work_types')->insert(array (
            0 =>
            array (
                'id' => 1,
                'parent_id' => NULL,
                'code' => 11,
                'name' => 'Agriculture, Forestry, Fishing and Hunting',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            1 =>
            array (
                'id' => 2,
                'parent_id' => 1,
                'code' => 111,
                'name' => 'Crop Production',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            2 =>
            array (
                'id' => 3,
                'parent_id' => 2,
                'code' => 1111,
                'name' => 'Oilseed and Grain Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            3 =>
            array (
                'id' => 4,
                'parent_id' => 3,
                'code' => 11111,
                'name' => 'Soybean Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            4 =>
            array (
                'id' => 5,
                'parent_id' => 4,
                'code' => 111110,
                'name' => 'Soybean Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:46:30',
                'has_child' => 0,
            ),
            5 =>
            array (
                'id' => 6,
                'parent_id' => 3,
                'code' => 11112,
            'name' => 'Oilseed (except Soybean) Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            6 =>
            array (
                'id' => 7,
                'parent_id' => 6,
                'code' => 111120,
            'name' => 'Oilseed (except Soybean) Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:46:32',
                'has_child' => 0,
            ),
            7 =>
            array (
                'id' => 8,
                'parent_id' => 3,
                'code' => 11113,
                'name' => 'Dry Pea and Bean Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            8 =>
            array (
                'id' => 9,
                'parent_id' => 8,
                'code' => 111130,
                'name' => 'Dry Pea and Bean Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:46:34',
                'has_child' => 0,
            ),
            9 =>
            array (
                'id' => 10,
                'parent_id' => 3,
                'code' => 11114,
                'name' => 'Wheat Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            10 =>
            array (
                'id' => 11,
                'parent_id' => 10,
                'code' => 111140,
                'name' => 'Wheat Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:46:36',
                'has_child' => 0,
            ),
            11 =>
            array (
                'id' => 12,
                'parent_id' => 3,
                'code' => 11115,
                'name' => 'Corn Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            12 =>
            array (
                'id' => 13,
                'parent_id' => 12,
                'code' => 111150,
                'name' => 'Corn Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:46:38',
                'has_child' => 0,
            ),
            13 =>
            array (
                'id' => 14,
                'parent_id' => 3,
                'code' => 11116,
                'name' => 'Rice Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            14 =>
            array (
                'id' => 15,
                'parent_id' => 14,
                'code' => 111160,
                'name' => 'Rice Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:46:40',
                'has_child' => 0,
            ),
            15 =>
            array (
                'id' => 16,
                'parent_id' => 3,
                'code' => 11119,
                'name' => 'Other Grain Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            16 =>
            array (
                'id' => 17,
                'parent_id' => 16,
                'code' => 111191,
                'name' => 'Oilseed and Grain Combination Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:46:42',
                'has_child' => 0,
            ),
            17 =>
            array (
                'id' => 18,
                'parent_id' => 16,
                'code' => 111199,
                'name' => 'All Other Grain Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:46:44',
                'has_child' => 0,
            ),
            18 =>
            array (
                'id' => 19,
                'parent_id' => 2,
                'code' => 1112,
                'name' => 'Vegetable and Melon Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            19 =>
            array (
                'id' => 20,
                'parent_id' => 19,
                'code' => 11121,
                'name' => 'Vegetable and Melon Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            20 =>
            array (
                'id' => 21,
                'parent_id' => 20,
                'code' => 111211,
                'name' => 'Potato Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:46:47',
                'has_child' => 0,
            ),
            21 =>
            array (
                'id' => 22,
                'parent_id' => 20,
                'code' => 111219,
            'name' => 'Other Vegetable (except Potato) and Melon Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:46:48',
                'has_child' => 0,
            ),
            22 =>
            array (
                'id' => 23,
                'parent_id' => 2,
                'code' => 1113,
                'name' => 'Fruit and Tree Nut Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            23 =>
            array (
                'id' => 24,
                'parent_id' => 23,
                'code' => 11131,
                'name' => 'Orange Groves',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            24 =>
            array (
                'id' => 25,
                'parent_id' => 24,
                'code' => 111310,
                'name' => 'Orange Groves',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:46:51',
                'has_child' => 0,
            ),
            25 =>
            array (
                'id' => 26,
                'parent_id' => 23,
                'code' => 11132,
            'name' => 'Citrus (except Orange) Groves',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            26 =>
            array (
                'id' => 27,
                'parent_id' => 26,
                'code' => 111320,
            'name' => 'Citrus (except Orange) Groves',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:46:53',
                'has_child' => 0,
            ),
            27 =>
            array (
                'id' => 28,
                'parent_id' => 23,
                'code' => 11133,
                'name' => 'Noncitrus Fruit and Tree Nut Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            28 =>
            array (
                'id' => 29,
                'parent_id' => 28,
                'code' => 111331,
                'name' => 'Apple Orchards',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:46:55',
                'has_child' => 0,
            ),
            29 =>
            array (
                'id' => 30,
                'parent_id' => 28,
                'code' => 111332,
                'name' => 'Grape Vineyards',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:46:56',
                'has_child' => 0,
            ),
            30 =>
            array (
                'id' => 31,
                'parent_id' => 28,
                'code' => 111333,
                'name' => 'Strawberry Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:46:57',
                'has_child' => 0,
            ),
            31 =>
            array (
                'id' => 32,
                'parent_id' => 28,
                'code' => 111334,
            'name' => 'Berry (except Strawberry) Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:46:58',
                'has_child' => 0,
            ),
            32 =>
            array (
                'id' => 33,
                'parent_id' => 28,
                'code' => 111335,
                'name' => 'Tree Nut Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:46:59',
                'has_child' => 0,
            ),
            33 =>
            array (
                'id' => 34,
                'parent_id' => 28,
                'code' => 111336,
                'name' => 'Fruit and Tree Nut Combination Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:00',
                'has_child' => 0,
            ),
            34 =>
            array (
                'id' => 35,
                'parent_id' => 28,
                'code' => 111339,
                'name' => 'Other Noncitrus Fruit Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:01',
                'has_child' => 0,
            ),
            35 =>
            array (
                'id' => 36,
                'parent_id' => 2,
                'code' => 1114,
                'name' => 'Greenhouse, Nursery, and Floriculture Production',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            36 =>
            array (
                'id' => 37,
                'parent_id' => 36,
                'code' => 11141,
                'name' => 'Food Crops Grown Under Cover',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            37 =>
            array (
                'id' => 38,
                'parent_id' => 37,
                'code' => 111411,
                'name' => 'Mushroom Production',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:05',
                'has_child' => 0,
            ),
            38 =>
            array (
                'id' => 39,
                'parent_id' => 37,
                'code' => 111419,
                'name' => 'Other Food Crops Grown Under Cover',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:06',
                'has_child' => 0,
            ),
            39 =>
            array (
                'id' => 40,
                'parent_id' => 36,
                'code' => 11142,
                'name' => 'Nursery and Floriculture Production',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            40 =>
            array (
                'id' => 41,
                'parent_id' => 40,
                'code' => 111421,
                'name' => 'Nursery and Tree Production',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:08',
                'has_child' => 0,
            ),
            41 =>
            array (
                'id' => 42,
                'parent_id' => 40,
                'code' => 111422,
                'name' => 'Floriculture Production',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:09',
                'has_child' => 0,
            ),
            42 =>
            array (
                'id' => 43,
                'parent_id' => 2,
                'code' => 1119,
                'name' => 'Other Crop Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            43 =>
            array (
                'id' => 44,
                'parent_id' => 43,
                'code' => 11191,
                'name' => 'Tobacco Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            44 =>
            array (
                'id' => 45,
                'parent_id' => 44,
                'code' => 111910,
                'name' => 'Tobacco Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:12',
                'has_child' => 0,
            ),
            45 =>
            array (
                'id' => 46,
                'parent_id' => 43,
                'code' => 11192,
                'name' => 'Cotton Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            46 =>
            array (
                'id' => 47,
                'parent_id' => 46,
                'code' => 111920,
                'name' => 'Cotton Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:14',
                'has_child' => 0,
            ),
            47 =>
            array (
                'id' => 48,
                'parent_id' => 43,
                'code' => 11193,
                'name' => 'Sugarcane Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            48 =>
            array (
                'id' => 49,
                'parent_id' => 48,
                'code' => 111930,
                'name' => 'Sugarcane Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:16',
                'has_child' => 0,
            ),
            49 =>
            array (
                'id' => 50,
                'parent_id' => 43,
                'code' => 11194,
                'name' => 'Hay Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            50 =>
            array (
                'id' => 51,
                'parent_id' => 50,
                'code' => 111940,
                'name' => 'Hay Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:18',
                'has_child' => 0,
            ),
            51 =>
            array (
                'id' => 52,
                'parent_id' => 43,
                'code' => 11199,
                'name' => 'All Other Crop Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            52 =>
            array (
                'id' => 53,
                'parent_id' => 52,
                'code' => 111991,
                'name' => 'Sugar Beet Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:20',
                'has_child' => 0,
            ),
            53 =>
            array (
                'id' => 54,
                'parent_id' => 52,
                'code' => 111992,
                'name' => 'Peanut Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:21',
                'has_child' => 0,
            ),
            54 =>
            array (
                'id' => 55,
                'parent_id' => 52,
                'code' => 111998,
                'name' => 'All Other Miscellaneous Crop Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:22',
                'has_child' => 0,
            ),
            55 =>
            array (
                'id' => 56,
                'parent_id' => 1,
                'code' => 112,
                'name' => 'Animal Production and Aquaculture',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            56 =>
            array (
                'id' => 57,
                'parent_id' => 56,
                'code' => 1121,
                'name' => 'Cattle Ranching and Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            57 =>
            array (
                'id' => 58,
                'parent_id' => 57,
                'code' => 11211,
                'name' => 'Beef Cattle Ranching and Farming, including Feedlots',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            58 =>
            array (
                'id' => 59,
                'parent_id' => 58,
                'code' => 112111,
                'name' => 'Beef Cattle Ranching and Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:26',
                'has_child' => 0,
            ),
            59 =>
            array (
                'id' => 60,
                'parent_id' => 58,
                'code' => 112112,
                'name' => 'Cattle Feedlots',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:27',
                'has_child' => 0,
            ),
            60 =>
            array (
                'id' => 61,
                'parent_id' => 57,
                'code' => 11212,
                'name' => 'Dairy Cattle and Milk Production',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            61 =>
            array (
                'id' => 62,
                'parent_id' => 61,
                'code' => 112120,
                'name' => 'Dairy Cattle and Milk Production',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:29',
                'has_child' => 0,
            ),
            62 =>
            array (
                'id' => 63,
                'parent_id' => 57,
                'code' => 11213,
                'name' => 'Dual-Purpose Cattle Ranching and Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            63 =>
            array (
                'id' => 64,
                'parent_id' => 63,
                'code' => 112130,
                'name' => 'Dual-Purpose Cattle Ranching and Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:31',
                'has_child' => 0,
            ),
            64 =>
            array (
                'id' => 65,
                'parent_id' => 56,
                'code' => 1122,
                'name' => 'Hog and Pig Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            65 =>
            array (
                'id' => 66,
                'parent_id' => 65,
                'code' => 11221,
                'name' => 'Hog and Pig Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            66 =>
            array (
                'id' => 67,
                'parent_id' => 66,
                'code' => 112210,
                'name' => 'Hog and Pig Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:34',
                'has_child' => 0,
            ),
            67 =>
            array (
                'id' => 68,
                'parent_id' => 56,
                'code' => 1123,
                'name' => 'Poultry and Egg Production',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            68 =>
            array (
                'id' => 69,
                'parent_id' => 68,
                'code' => 11231,
                'name' => 'Chicken Egg Production',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            69 =>
            array (
                'id' => 70,
                'parent_id' => 69,
                'code' => 112310,
                'name' => 'Chicken Egg Production',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:37',
                'has_child' => 0,
            ),
            70 =>
            array (
                'id' => 71,
                'parent_id' => 68,
                'code' => 11232,
                'name' => 'Broilers and Other Meat Type Chicken Production',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            71 =>
            array (
                'id' => 72,
                'parent_id' => 71,
                'code' => 112320,
                'name' => 'Broilers and Other Meat Type Chicken Production',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:39',
                'has_child' => 0,
            ),
            72 =>
            array (
                'id' => 73,
                'parent_id' => 68,
                'code' => 11233,
                'name' => 'Turkey Production',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            73 =>
            array (
                'id' => 74,
                'parent_id' => 73,
                'code' => 112330,
                'name' => 'Turkey Production',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:41',
                'has_child' => 0,
            ),
            74 =>
            array (
                'id' => 75,
                'parent_id' => 68,
                'code' => 11234,
                'name' => 'Poultry Hatcheries',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            75 =>
            array (
                'id' => 76,
                'parent_id' => 75,
                'code' => 112340,
                'name' => 'Poultry Hatcheries',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:42',
                'has_child' => 0,
            ),
            76 =>
            array (
                'id' => 77,
                'parent_id' => 68,
                'code' => 11239,
                'name' => 'Other Poultry Production',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            77 =>
            array (
                'id' => 78,
                'parent_id' => 77,
                'code' => 112390,
                'name' => 'Other Poultry Production',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:44',
                'has_child' => 0,
            ),
            78 =>
            array (
                'id' => 79,
                'parent_id' => 56,
                'code' => 1124,
                'name' => 'Sheep and Goat Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            79 =>
            array (
                'id' => 80,
                'parent_id' => 79,
                'code' => 11241,
                'name' => 'Sheep Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            80 =>
            array (
                'id' => 81,
                'parent_id' => 80,
                'code' => 112410,
                'name' => 'Sheep Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:47',
                'has_child' => 0,
            ),
            81 =>
            array (
                'id' => 82,
                'parent_id' => 79,
                'code' => 11242,
                'name' => 'Goat Farming',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            82 =>
            array (
                'id' => 83,
                'parent_id' => 82,
                'code' => 112420,
                'name' => 'Goat Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:49',
                'has_child' => 0,
            ),
            83 =>
            array (
                'id' => 84,
                'parent_id' => 56,
                'code' => 1125,
                'name' => 'Aquaculture',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            84 =>
            array (
                'id' => 85,
                'parent_id' => 84,
                'code' => 11251,
                'name' => 'Aquaculture',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            85 =>
            array (
                'id' => 86,
                'parent_id' => 85,
                'code' => 112511,
                'name' => 'Finfish Farming and Fish Hatcheries',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:52',
                'has_child' => 0,
            ),
            86 =>
            array (
                'id' => 87,
                'parent_id' => 85,
                'code' => 112512,
                'name' => 'Shellfish Farming',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:53',
                'has_child' => 0,
            ),
            87 =>
            array (
                'id' => 88,
                'parent_id' => 85,
                'code' => 112519,
                'name' => 'Other Aquaculture',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:54',
                'has_child' => 0,
            ),
            88 =>
            array (
                'id' => 89,
                'parent_id' => 56,
                'code' => 1129,
                'name' => 'Other Animal Production',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            89 =>
            array (
                'id' => 90,
                'parent_id' => 89,
                'code' => 11291,
                'name' => 'Apiculture',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            90 =>
            array (
                'id' => 91,
                'parent_id' => 90,
                'code' => 112910,
                'name' => 'Apiculture',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:57',
                'has_child' => 0,
            ),
            91 =>
            array (
                'id' => 92,
                'parent_id' => 89,
                'code' => 11292,
                'name' => 'Horses and Other Equine Production',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            92 =>
            array (
                'id' => 93,
                'parent_id' => 92,
                'code' => 112920,
                'name' => 'Horses and Other Equine Production',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:47:59',
                'has_child' => 0,
            ),
            93 =>
            array (
                'id' => 94,
                'parent_id' => 89,
                'code' => 11293,
                'name' => 'Fur-Bearing Animal and Rabbit Production',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            94 =>
            array (
                'id' => 95,
                'parent_id' => 94,
                'code' => 112930,
                'name' => 'Fur-Bearing Animal and Rabbit Production',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:01',
                'has_child' => 0,
            ),
            95 =>
            array (
                'id' => 96,
                'parent_id' => 89,
                'code' => 11299,
                'name' => 'All Other Animal Production',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            96 =>
            array (
                'id' => 97,
                'parent_id' => 96,
                'code' => 112990,
                'name' => 'All Other Animal Production',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:03',
                'has_child' => 0,
            ),
            97 =>
            array (
                'id' => 98,
                'parent_id' => 1,
                'code' => 113,
                'name' => 'Forestry and Logging',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            98 =>
            array (
                'id' => 99,
                'parent_id' => 98,
                'code' => 1131,
                'name' => 'Timber Tract Operations',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            99 =>
            array (
                'id' => 100,
                'parent_id' => 99,
                'code' => 11311,
                'name' => 'Timber Tract Operations',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            100 =>
            array (
                'id' => 101,
                'parent_id' => 100,
                'code' => 113110,
                'name' => 'Timber Tract Operations',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:07',
                'has_child' => 0,
            ),
            101 =>
            array (
                'id' => 102,
                'parent_id' => 98,
                'code' => 1132,
                'name' => 'Forest Nurseries and Gathering of Forest Products',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            102 =>
            array (
                'id' => 103,
                'parent_id' => 102,
                'code' => 11321,
                'name' => 'Forest Nurseries and Gathering of Forest Products',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            103 =>
            array (
                'id' => 104,
                'parent_id' => 103,
                'code' => 113210,
                'name' => 'Forest Nurseries and Gathering of Forest Products',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:09',
                'has_child' => 0,
            ),
            104 =>
            array (
                'id' => 105,
                'parent_id' => 98,
                'code' => 1133,
                'name' => 'Logging',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            105 =>
            array (
                'id' => 106,
                'parent_id' => 105,
                'code' => 11331,
                'name' => 'Logging',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            106 =>
            array (
                'id' => 107,
                'parent_id' => 106,
                'code' => 113310,
                'name' => 'Logging',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:12',
                'has_child' => 0,
            ),
            107 =>
            array (
                'id' => 108,
                'parent_id' => 1,
                'code' => 114,
                'name' => 'Fishing, Hunting and Trapping',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            108 =>
            array (
                'id' => 109,
                'parent_id' => 108,
                'code' => 1141,
                'name' => 'Fishing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            109 =>
            array (
                'id' => 110,
                'parent_id' => 109,
                'code' => 11411,
                'name' => 'Fishing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            110 =>
            array (
                'id' => 111,
                'parent_id' => 110,
                'code' => 114111,
                'name' => 'Finfish Fishing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:16',
                'has_child' => 0,
            ),
            111 =>
            array (
                'id' => 112,
                'parent_id' => 110,
                'code' => 114112,
                'name' => 'Shellfish Fishing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:17',
                'has_child' => 0,
            ),
            112 =>
            array (
                'id' => 113,
                'parent_id' => 110,
                'code' => 114119,
                'name' => 'Other Marine Fishing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:18',
                'has_child' => 0,
            ),
            113 =>
            array (
                'id' => 114,
                'parent_id' => 108,
                'code' => 1142,
                'name' => 'Hunting and Trapping',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            114 =>
            array (
                'id' => 115,
                'parent_id' => 114,
                'code' => 11421,
                'name' => 'Hunting and Trapping',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            115 =>
            array (
                'id' => 116,
                'parent_id' => 115,
                'code' => 114210,
                'name' => 'Hunting and Trapping',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:21',
                'has_child' => 0,
            ),
            116 =>
            array (
                'id' => 117,
                'parent_id' => 1,
                'code' => 115,
                'name' => 'Support Activities for Agriculture and Forestry',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            117 =>
            array (
                'id' => 118,
                'parent_id' => 117,
                'code' => 1151,
                'name' => 'Support Activities for Crop Production',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            118 =>
            array (
                'id' => 119,
                'parent_id' => 118,
                'code' => 11511,
                'name' => 'Support Activities for Crop Production',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            119 =>
            array (
                'id' => 120,
                'parent_id' => 119,
                'code' => 115111,
                'name' => 'Cotton Ginning',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:25',
                'has_child' => 0,
            ),
            120 =>
            array (
                'id' => 121,
                'parent_id' => 119,
                'code' => 115112,
                'name' => 'Soil Preparation, Planting, and Cultivating',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:26',
                'has_child' => 0,
            ),
            121 =>
            array (
                'id' => 122,
                'parent_id' => 119,
                'code' => 115113,
                'name' => 'Crop Harvesting, Primarily by Machine',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:27',
                'has_child' => 0,
            ),
            122 =>
            array (
                'id' => 123,
                'parent_id' => 119,
                'code' => 115114,
            'name' => 'Postharvest Crop Activities (except Cotton Ginning)',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:28',
                'has_child' => 0,
            ),
            123 =>
            array (
                'id' => 124,
                'parent_id' => 119,
                'code' => 115115,
                'name' => 'Farm Labor Contractors and Crew Leaders',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:29',
                'has_child' => 0,
            ),
            124 =>
            array (
                'id' => 125,
                'parent_id' => 119,
                'code' => 115116,
                'name' => 'Farm Management Services',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:30',
                'has_child' => 0,
            ),
            125 =>
            array (
                'id' => 126,
                'parent_id' => 117,
                'code' => 1152,
                'name' => 'Support Activities for Animal Production',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            126 =>
            array (
                'id' => 127,
                'parent_id' => 126,
                'code' => 11521,
                'name' => 'Support Activities for Animal Production',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            127 =>
            array (
                'id' => 128,
                'parent_id' => 127,
                'code' => 115210,
                'name' => 'Support Activities for Animal Production',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:32',
                'has_child' => 0,
            ),
            128 =>
            array (
                'id' => 129,
                'parent_id' => 117,
                'code' => 1153,
                'name' => 'Support Activities for Forestry',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            129 =>
            array (
                'id' => 130,
                'parent_id' => 129,
                'code' => 11531,
                'name' => 'Support Activities for Forestry',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            130 =>
            array (
                'id' => 131,
                'parent_id' => 130,
                'code' => 115310,
                'name' => 'Support Activities for Forestry',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:35',
                'has_child' => 0,
            ),
            131 =>
            array (
                'id' => 132,
                'parent_id' => NULL,
                'code' => 21,
                'name' => 'Mining, Quarrying, and Oil and Gas Extraction',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            132 =>
            array (
                'id' => 133,
                'parent_id' => 132,
                'code' => 211,
                'name' => 'Oil and Gas Extraction',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            133 =>
            array (
                'id' => 134,
                'parent_id' => 133,
                'code' => 2111,
                'name' => 'Oil and Gas Extraction',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            134 =>
            array (
                'id' => 135,
                'parent_id' => 134,
                'code' => 21111,
                'name' => 'Oil and Gas Extraction',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            135 =>
            array (
                'id' => 136,
                'parent_id' => 135,
                'code' => 211111,
                'name' => 'Crude Petroleum and Natural Gas Extraction',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:40',
                'has_child' => 0,
            ),
            136 =>
            array (
                'id' => 137,
                'parent_id' => 135,
                'code' => 211112,
                'name' => 'Natural Gas Liquid Extraction',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:41',
                'has_child' => 0,
            ),
            137 =>
            array (
                'id' => 138,
                'parent_id' => 132,
                'code' => 212,
            'name' => 'Mining (except Oil and Gas)',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            138 =>
            array (
                'id' => 139,
                'parent_id' => 138,
                'code' => 2121,
                'name' => 'Coal Mining',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            139 =>
            array (
                'id' => 140,
                'parent_id' => 139,
                'code' => 21211,
                'name' => 'Coal Mining',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            140 =>
            array (
                'id' => 141,
                'parent_id' => 140,
                'code' => 212111,
                'name' => 'Bituminous Coal and Lignite Surface Mining',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:45',
                'has_child' => 0,
            ),
            141 =>
            array (
                'id' => 142,
                'parent_id' => 140,
                'code' => 212112,
                'name' => 'Bituminous Coal Underground Mining',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:46',
                'has_child' => 0,
            ),
            142 =>
            array (
                'id' => 143,
                'parent_id' => 140,
                'code' => 212113,
                'name' => 'Anthracite Mining',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:47',
                'has_child' => 0,
            ),
            143 =>
            array (
                'id' => 144,
                'parent_id' => 138,
                'code' => 2122,
                'name' => 'Metal Ore Mining',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            144 =>
            array (
                'id' => 145,
                'parent_id' => 144,
                'code' => 21221,
                'name' => 'Iron Ore Mining',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            145 =>
            array (
                'id' => 146,
                'parent_id' => 145,
                'code' => 212210,
                'name' => 'Iron Ore Mining',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:50',
                'has_child' => 0,
            ),
            146 =>
            array (
                'id' => 147,
                'parent_id' => 144,
                'code' => 21222,
                'name' => 'Gold Ore and Silver Ore Mining',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            147 =>
            array (
                'id' => 148,
                'parent_id' => 147,
                'code' => 212221,
                'name' => 'Gold Ore Mining',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:52',
                'has_child' => 0,
            ),
            148 =>
            array (
                'id' => 149,
                'parent_id' => 147,
                'code' => 212222,
                'name' => 'Silver Ore Mining',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:53',
                'has_child' => 0,
            ),
            149 =>
            array (
                'id' => 150,
                'parent_id' => 144,
                'code' => 21223,
                'name' => 'Copper, Nickel, Lead, and Zinc Mining',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            150 =>
            array (
                'id' => 151,
                'parent_id' => 150,
                'code' => 212231,
                'name' => 'Lead Ore and Zinc Ore Mining',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:54',
                'has_child' => 0,
            ),
            151 =>
            array (
                'id' => 152,
                'parent_id' => 150,
                'code' => 212234,
                'name' => 'Copper Ore and Nickel Ore Mining',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:55',
                'has_child' => 0,
            ),
            152 =>
            array (
                'id' => 153,
                'parent_id' => 144,
                'code' => 21229,
                'name' => 'Other Metal Ore Mining',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            153 =>
            array (
                'id' => 154,
                'parent_id' => 153,
                'code' => 212291,
                'name' => 'Uranium-Radium-Vanadium Ore Mining',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:57',
                'has_child' => 0,
            ),
            154 =>
            array (
                'id' => 155,
                'parent_id' => 153,
                'code' => 212299,
                'name' => 'All Other Metal Ore Mining',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:48:58',
                'has_child' => 0,
            ),
            155 =>
            array (
                'id' => 156,
                'parent_id' => 138,
                'code' => 2123,
                'name' => 'Nonmetallic Mineral Mining and Quarrying',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            156 =>
            array (
                'id' => 157,
                'parent_id' => 156,
                'code' => 21231,
                'name' => 'Stone Mining and Quarrying',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            157 =>
            array (
                'id' => 158,
                'parent_id' => 157,
                'code' => 212311,
                'name' => 'Dimension Stone Mining and Quarrying',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:01',
                'has_child' => 0,
            ),
            158 =>
            array (
                'id' => 159,
                'parent_id' => 157,
                'code' => 212312,
                'name' => 'Crushed and Broken Limestone Mining and Quarrying',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:02',
                'has_child' => 0,
            ),
            159 =>
            array (
                'id' => 160,
                'parent_id' => 157,
                'code' => 212313,
                'name' => 'Crushed and Broken Granite Mining and Quarrying',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:03',
                'has_child' => 0,
            ),
            160 =>
            array (
                'id' => 161,
                'parent_id' => 157,
                'code' => 212319,
                'name' => 'Other Crushed and Broken Stone Mining and Quarrying',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:04',
                'has_child' => 0,
            ),
            161 =>
            array (
                'id' => 162,
                'parent_id' => 156,
                'code' => 21232,
                'name' => 'Sand, Gravel, Clay, and Ceramic and Refractory Minerals Mining and Quarrying',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            162 =>
            array (
                'id' => 163,
                'parent_id' => 162,
                'code' => 212321,
                'name' => 'Construction Sand and Gravel Mining',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:06',
                'has_child' => 0,
            ),
            163 =>
            array (
                'id' => 164,
                'parent_id' => 162,
                'code' => 212322,
                'name' => 'Industrial Sand Mining',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:07',
                'has_child' => 0,
            ),
            164 =>
            array (
                'id' => 165,
                'parent_id' => 162,
                'code' => 212324,
                'name' => 'Kaolin and Ball Clay Mining',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:08',
                'has_child' => 0,
            ),
            165 =>
            array (
                'id' => 166,
                'parent_id' => 162,
                'code' => 212325,
                'name' => 'Clay and Ceramic and Refractory Minerals Mining',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:09',
                'has_child' => 0,
            ),
            166 =>
            array (
                'id' => 167,
                'parent_id' => 156,
                'code' => 21239,
                'name' => 'Other Nonmetallic Mineral Mining and Quarrying',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            167 =>
            array (
                'id' => 168,
                'parent_id' => 167,
                'code' => 212391,
                'name' => 'Potash, Soda, and Borate Mineral Mining',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:11',
                'has_child' => 0,
            ),
            168 =>
            array (
                'id' => 169,
                'parent_id' => 167,
                'code' => 212392,
                'name' => 'Phosphate Rock Mining',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:12',
                'has_child' => 0,
            ),
            169 =>
            array (
                'id' => 170,
                'parent_id' => 167,
                'code' => 212393,
                'name' => 'Other Chemical and Fertilizer Mineral Mining',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:13',
                'has_child' => 0,
            ),
            170 =>
            array (
                'id' => 171,
                'parent_id' => 167,
                'code' => 212399,
                'name' => 'All Other Nonmetallic Mineral Mining',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:14',
                'has_child' => 0,
            ),
            171 =>
            array (
                'id' => 172,
                'parent_id' => 132,
                'code' => 213,
                'name' => 'Support Activities for Mining',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            172 =>
            array (
                'id' => 173,
                'parent_id' => 172,
                'code' => 2131,
                'name' => 'Support Activities for Mining',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            173 =>
            array (
                'id' => 174,
                'parent_id' => 173,
                'code' => 21311,
                'name' => 'Support Activities for Mining',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            174 =>
            array (
                'id' => 175,
                'parent_id' => 174,
                'code' => 213111,
                'name' => 'Drilling Oil and Gas Wells',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:17',
                'has_child' => 0,
            ),
            175 =>
            array (
                'id' => 176,
                'parent_id' => 174,
                'code' => 213112,
                'name' => 'Support Activities for Oil and Gas Operations',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:18',
                'has_child' => 0,
            ),
            176 =>
            array (
                'id' => 177,
                'parent_id' => 174,
                'code' => 213113,
                'name' => 'Support Activities for Coal Mining',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:19',
                'has_child' => 0,
            ),
            177 =>
            array (
                'id' => 178,
                'parent_id' => 174,
                'code' => 213114,
                'name' => 'Support Activities for Metal Mining',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:20',
                'has_child' => 0,
            ),
            178 =>
            array (
                'id' => 179,
                'parent_id' => 174,
                'code' => 213115,
            'name' => 'Support Activities for Nonmetallic Minerals (except Fuels) Mining',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:21',
                'has_child' => 0,
            ),
            179 =>
            array (
                'id' => 180,
                'parent_id' => NULL,
                'code' => 22,
                'name' => 'Utilities',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            180 =>
            array (
                'id' => 181,
                'parent_id' => 180,
                'code' => 221,
                'name' => 'Utilities',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            181 =>
            array (
                'id' => 182,
                'parent_id' => 181,
                'code' => 2211,
                'name' => 'Electric Power Generation, Transmission and Distribution',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            182 =>
            array (
                'id' => 183,
                'parent_id' => 182,
                'code' => 22111,
                'name' => 'Electric Power Generation',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            183 =>
            array (
                'id' => 184,
                'parent_id' => 183,
                'code' => 221111,
                'name' => 'Hydroelectric Power Generation',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:26',
                'has_child' => 0,
            ),
            184 =>
            array (
                'id' => 185,
                'parent_id' => 183,
                'code' => 221112,
                'name' => 'Fossil Fuel Electric Power Generation',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:27',
                'has_child' => 0,
            ),
            185 =>
            array (
                'id' => 186,
                'parent_id' => 183,
                'code' => 221113,
                'name' => 'Nuclear Electric Power Generation',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:28',
                'has_child' => 0,
            ),
            186 =>
            array (
                'id' => 187,
                'parent_id' => 183,
                'code' => 221114,
                'name' => 'Solar Electric Power Generation',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:29',
                'has_child' => 0,
            ),
            187 =>
            array (
                'id' => 188,
                'parent_id' => 183,
                'code' => 221115,
                'name' => 'Wind Electric Power Generation',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:30',
                'has_child' => 0,
            ),
            188 =>
            array (
                'id' => 189,
                'parent_id' => 183,
                'code' => 221116,
                'name' => 'Geothermal Electric Power Generation',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:31',
                'has_child' => 0,
            ),
            189 =>
            array (
                'id' => 190,
                'parent_id' => 183,
                'code' => 221117,
                'name' => 'Biomass Electric Power Generation',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:32',
                'has_child' => 0,
            ),
            190 =>
            array (
                'id' => 191,
                'parent_id' => 183,
                'code' => 221118,
                'name' => 'Other Electric Power Generation',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:33',
                'has_child' => 0,
            ),
            191 =>
            array (
                'id' => 192,
                'parent_id' => 182,
                'code' => 22112,
                'name' => 'Electric Power Transmission, Control, and Distribution',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            192 =>
            array (
                'id' => 193,
                'parent_id' => 192,
                'code' => 221121,
                'name' => 'Electric Bulk Power Transmission and Control',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:35',
                'has_child' => 0,
            ),
            193 =>
            array (
                'id' => 194,
                'parent_id' => 192,
                'code' => 221122,
                'name' => 'Electric Power Distribution',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:36',
                'has_child' => 0,
            ),
            194 =>
            array (
                'id' => 195,
                'parent_id' => 181,
                'code' => 2212,
                'name' => 'Natural Gas Distribution',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            195 =>
            array (
                'id' => 196,
                'parent_id' => 195,
                'code' => 22121,
                'name' => 'Natural Gas Distribution',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            196 =>
            array (
                'id' => 197,
                'parent_id' => 196,
                'code' => 221210,
                'name' => 'Natural Gas Distribution',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:39',
                'has_child' => 0,
            ),
            197 =>
            array (
                'id' => 198,
                'parent_id' => 181,
                'code' => 2213,
                'name' => 'Water, Sewage and Other Systems',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            198 =>
            array (
                'id' => 199,
                'parent_id' => 198,
                'code' => 22131,
                'name' => 'Water Supply and Irrigation Systems',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            199 =>
            array (
                'id' => 200,
                'parent_id' => 199,
                'code' => 221310,
                'name' => 'Water Supply and Irrigation Systems',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:41',
                'has_child' => 0,
            ),
            200 =>
            array (
                'id' => 201,
                'parent_id' => 198,
                'code' => 22132,
                'name' => 'Sewage Treatment Facilities',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            201 =>
            array (
                'id' => 202,
                'parent_id' => 201,
                'code' => 221320,
                'name' => 'Sewage Treatment Facilities',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:43',
                'has_child' => 0,
            ),
            202 =>
            array (
                'id' => 203,
                'parent_id' => 198,
                'code' => 22133,
                'name' => 'Steam and Air-Conditioning Supply',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            203 =>
            array (
                'id' => 204,
                'parent_id' => 203,
                'code' => 221330,
                'name' => 'Steam and Air-Conditioning Supply',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:45',
                'has_child' => 0,
            ),
            204 =>
            array (
                'id' => 205,
                'parent_id' => NULL,
                'code' => 23,
                'name' => 'Construction',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            205 =>
            array (
                'id' => 206,
                'parent_id' => 205,
                'code' => 236,
                'name' => 'Construction of Buildings',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            206 =>
            array (
                'id' => 207,
                'parent_id' => 206,
                'code' => 2361,
                'name' => 'Residential Building Construction',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            207 =>
            array (
                'id' => 208,
                'parent_id' => 207,
                'code' => 23611,
                'name' => 'Residential Building Construction',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            208 =>
            array (
                'id' => 209,
                'parent_id' => 208,
                'code' => 236115,
            'name' => 'New Single-Family Housing Construction (except For-Sale Builders)',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:50',
                'has_child' => 0,
            ),
            209 =>
            array (
                'id' => 210,
                'parent_id' => 208,
                'code' => 236116,
            'name' => 'New Multifamily Housing Construction (except For-Sale Builders)',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:51',
                'has_child' => 0,
            ),
            210 =>
            array (
                'id' => 211,
                'parent_id' => 208,
                'code' => 236117,
                'name' => 'New Housing For-Sale Builders',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:52',
                'has_child' => 0,
            ),
            211 =>
            array (
                'id' => 212,
                'parent_id' => 208,
                'code' => 236118,
                'name' => 'Residential Remodelers',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:53',
                'has_child' => 0,
            ),
            212 =>
            array (
                'id' => 213,
                'parent_id' => 206,
                'code' => 2362,
                'name' => 'Nonresidential Building Construction',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            213 =>
            array (
                'id' => 214,
                'parent_id' => 213,
                'code' => 23621,
                'name' => 'Industrial Building Construction',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            214 =>
            array (
                'id' => 215,
                'parent_id' => 214,
                'code' => 236210,
                'name' => 'Industrial Building Construction',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:56',
                'has_child' => 0,
            ),
            215 =>
            array (
                'id' => 216,
                'parent_id' => 213,
                'code' => 23622,
                'name' => 'Commercial and Institutional Building Construction',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            216 =>
            array (
                'id' => 217,
                'parent_id' => 216,
                'code' => 236220,
                'name' => 'Commercial and Institutional Building Construction',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:49:58',
                'has_child' => 0,
            ),
            217 =>
            array (
                'id' => 218,
                'parent_id' => 205,
                'code' => 237,
                'name' => 'Heavy and Civil Engineering Construction',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            218 =>
            array (
                'id' => 219,
                'parent_id' => 218,
                'code' => 2371,
                'name' => 'Utility System Construction',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            219 =>
            array (
                'id' => 220,
                'parent_id' => 219,
                'code' => 23711,
                'name' => 'Water and Sewer Line and Related Structures Construction',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            220 =>
            array (
                'id' => 221,
                'parent_id' => 220,
                'code' => 237110,
                'name' => 'Water and Sewer Line and Related Structures Construction',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:02',
                'has_child' => 0,
            ),
            221 =>
            array (
                'id' => 222,
                'parent_id' => 219,
                'code' => 23712,
                'name' => 'Oil and Gas Pipeline and Related Structures Construction',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            222 =>
            array (
                'id' => 223,
                'parent_id' => 222,
                'code' => 237120,
                'name' => 'Oil and Gas Pipeline and Related Structures Construction',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:04',
                'has_child' => 0,
            ),
            223 =>
            array (
                'id' => 224,
                'parent_id' => 219,
                'code' => 23713,
                'name' => 'Power and Communication Line and Related Structures Construction',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            224 =>
            array (
                'id' => 225,
                'parent_id' => 224,
                'code' => 237130,
                'name' => 'Power and Communication Line and Related Structures Construction',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:06',
                'has_child' => 0,
            ),
            225 =>
            array (
                'id' => 226,
                'parent_id' => 218,
                'code' => 2372,
                'name' => 'Land Subdivision',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            226 =>
            array (
                'id' => 227,
                'parent_id' => 226,
                'code' => 23721,
                'name' => 'Land Subdivision',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            227 =>
            array (
                'id' => 228,
                'parent_id' => 227,
                'code' => 237210,
                'name' => 'Land Subdivision',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:08',
                'has_child' => 0,
            ),
            228 =>
            array (
                'id' => 229,
                'parent_id' => 218,
                'code' => 2373,
                'name' => 'Highway, Street, and Bridge Construction',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            229 =>
            array (
                'id' => 230,
                'parent_id' => 229,
                'code' => 23731,
                'name' => 'Highway, Street, and Bridge Construction',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            230 =>
            array (
                'id' => 231,
                'parent_id' => 230,
                'code' => 237310,
                'name' => 'Highway, Street, and Bridge Construction',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:11',
                'has_child' => 0,
            ),
            231 =>
            array (
                'id' => 232,
                'parent_id' => 218,
                'code' => 2379,
                'name' => 'Other Heavy and Civil Engineering Construction',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            232 =>
            array (
                'id' => 233,
                'parent_id' => 232,
                'code' => 23799,
                'name' => 'Other Heavy and Civil Engineering Construction',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            233 =>
            array (
                'id' => 234,
                'parent_id' => 233,
                'code' => 237990,
                'name' => 'Other Heavy and Civil Engineering Construction',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:14',
                'has_child' => 0,
            ),
            234 =>
            array (
                'id' => 235,
                'parent_id' => 205,
                'code' => 238,
                'name' => 'Specialty Trade Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            235 =>
            array (
                'id' => 236,
                'parent_id' => 235,
                'code' => 2381,
                'name' => 'Foundation, Structure, and Building Exterior Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            236 =>
            array (
                'id' => 237,
                'parent_id' => 236,
                'code' => 23811,
                'name' => 'Poured Concrete Foundation and Structure Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            237 =>
            array (
                'id' => 238,
                'parent_id' => 237,
                'code' => 238110,
                'name' => 'Poured Concrete Foundation and Structure Contractors',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:18',
                'has_child' => 0,
            ),
            238 =>
            array (
                'id' => 239,
                'parent_id' => 236,
                'code' => 23812,
                'name' => 'Structural Steel and Precast Concrete Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            239 =>
            array (
                'id' => 240,
                'parent_id' => 239,
                'code' => 238120,
                'name' => 'Structural Steel and Precast Concrete Contractors',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:20',
                'has_child' => 0,
            ),
            240 =>
            array (
                'id' => 241,
                'parent_id' => 236,
                'code' => 23813,
                'name' => 'Framing Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            241 =>
            array (
                'id' => 242,
                'parent_id' => 241,
                'code' => 238130,
                'name' => 'Framing Contractors',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:22',
                'has_child' => 0,
            ),
            242 =>
            array (
                'id' => 243,
                'parent_id' => 236,
                'code' => 23814,
                'name' => 'Masonry Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            243 =>
            array (
                'id' => 244,
                'parent_id' => 243,
                'code' => 238140,
                'name' => 'Masonry Contractors',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:24',
                'has_child' => 0,
            ),
            244 =>
            array (
                'id' => 245,
                'parent_id' => 236,
                'code' => 23815,
                'name' => 'Glass and Glazing Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            245 =>
            array (
                'id' => 246,
                'parent_id' => 245,
                'code' => 238150,
                'name' => 'Glass and Glazing Contractors',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:26',
                'has_child' => 0,
            ),
            246 =>
            array (
                'id' => 247,
                'parent_id' => 236,
                'code' => 23816,
                'name' => 'Roofing Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            247 =>
            array (
                'id' => 248,
                'parent_id' => 247,
                'code' => 238160,
                'name' => 'Roofing Contractors',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:28',
                'has_child' => 0,
            ),
            248 =>
            array (
                'id' => 249,
                'parent_id' => 236,
                'code' => 23817,
                'name' => 'Siding Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            249 =>
            array (
                'id' => 250,
                'parent_id' => 249,
                'code' => 238170,
                'name' => 'Siding Contractors',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:30',
                'has_child' => 0,
            ),
            250 =>
            array (
                'id' => 251,
                'parent_id' => 236,
                'code' => 23819,
                'name' => 'Other Foundation, Structure, and Building Exterior Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            251 =>
            array (
                'id' => 252,
                'parent_id' => 251,
                'code' => 238190,
                'name' => 'Other Foundation, Structure, and Building Exterior Contractors',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:32',
                'has_child' => 0,
            ),
            252 =>
            array (
                'id' => 253,
                'parent_id' => 235,
                'code' => 2382,
                'name' => 'Building Equipment Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            253 =>
            array (
                'id' => 254,
                'parent_id' => 253,
                'code' => 23821,
                'name' => 'Electrical Contractors and Other Wiring Installation Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            254 =>
            array (
                'id' => 255,
                'parent_id' => 254,
                'code' => 238210,
                'name' => 'Electrical Contractors and Other Wiring Installation Contractors',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:34',
                'has_child' => 0,
            ),
            255 =>
            array (
                'id' => 256,
                'parent_id' => 253,
                'code' => 23822,
                'name' => 'Plumbing, Heating, and Air-Conditioning Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            256 =>
            array (
                'id' => 257,
                'parent_id' => 256,
                'code' => 238220,
                'name' => 'Plumbing, Heating, and Air-Conditioning Contractors',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:36',
                'has_child' => 0,
            ),
            257 =>
            array (
                'id' => 258,
                'parent_id' => 253,
                'code' => 23829,
                'name' => 'Other Building Equipment Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            258 =>
            array (
                'id' => 259,
                'parent_id' => 258,
                'code' => 238290,
                'name' => 'Other Building Equipment Contractors',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:38',
                'has_child' => 0,
            ),
            259 =>
            array (
                'id' => 260,
                'parent_id' => 235,
                'code' => 2383,
                'name' => 'Building Finishing Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            260 =>
            array (
                'id' => 261,
                'parent_id' => 260,
                'code' => 23831,
                'name' => 'Drywall and Insulation Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            261 =>
            array (
                'id' => 262,
                'parent_id' => 261,
                'code' => 238310,
                'name' => 'Drywall and Insulation Contractors',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:41',
                'has_child' => 0,
            ),
            262 =>
            array (
                'id' => 263,
                'parent_id' => 260,
                'code' => 23832,
                'name' => 'Painting and Wall Covering Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            263 =>
            array (
                'id' => 264,
                'parent_id' => 263,
                'code' => 238320,
                'name' => 'Painting and Wall Covering Contractors',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:43',
                'has_child' => 0,
            ),
            264 =>
            array (
                'id' => 265,
                'parent_id' => 260,
                'code' => 23833,
                'name' => 'Flooring Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            265 =>
            array (
                'id' => 266,
                'parent_id' => 265,
                'code' => 238330,
                'name' => 'Flooring Contractors',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:45',
                'has_child' => 0,
            ),
            266 =>
            array (
                'id' => 267,
                'parent_id' => 260,
                'code' => 23834,
                'name' => 'Tile and Terrazzo Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            267 =>
            array (
                'id' => 268,
                'parent_id' => 267,
                'code' => 238340,
                'name' => 'Tile and Terrazzo Contractors',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:47',
                'has_child' => 0,
            ),
            268 =>
            array (
                'id' => 269,
                'parent_id' => 260,
                'code' => 23835,
                'name' => 'Finish Carpentry Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            269 =>
            array (
                'id' => 270,
                'parent_id' => 269,
                'code' => 238350,
                'name' => 'Finish Carpentry Contractors',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:49',
                'has_child' => 0,
            ),
            270 =>
            array (
                'id' => 271,
                'parent_id' => 260,
                'code' => 23839,
                'name' => 'Other Building Finishing Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            271 =>
            array (
                'id' => 272,
                'parent_id' => 271,
                'code' => 238390,
                'name' => 'Other Building Finishing Contractors',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:51',
                'has_child' => 0,
            ),
            272 =>
            array (
                'id' => 273,
                'parent_id' => 235,
                'code' => 2389,
                'name' => 'Other Specialty Trade Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            273 =>
            array (
                'id' => 274,
                'parent_id' => 273,
                'code' => 23891,
                'name' => 'Site Preparation Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            274 =>
            array (
                'id' => 275,
                'parent_id' => 274,
                'code' => 238910,
                'name' => 'Site Preparation Contractors',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:54',
                'has_child' => 0,
            ),
            275 =>
            array (
                'id' => 276,
                'parent_id' => 273,
                'code' => 23899,
                'name' => 'All Other Specialty Trade Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            276 =>
            array (
                'id' => 277,
                'parent_id' => 276,
                'code' => 238990,
                'name' => 'All Other Specialty Trade Contractors',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:50:56',
                'has_child' => 0,
            ),
            277 =>
            array (
                'id' => 278,
                'parent_id' => NULL,
                'code' => 31,
                'name' => 'Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            278 =>
            array (
                'id' => 279,
                'parent_id' => 278,
                'code' => 311,
                'name' => 'Food Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            279 =>
            array (
                'id' => 280,
                'parent_id' => 279,
                'code' => 3111,
                'name' => 'Animal Food Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            280 =>
            array (
                'id' => 281,
                'parent_id' => 280,
                'code' => 31111,
                'name' => 'Animal Food Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            281 =>
            array (
                'id' => 282,
                'parent_id' => 281,
                'code' => 311111,
                'name' => 'Dog and Cat Food Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:00',
                'has_child' => 0,
            ),
            282 =>
            array (
                'id' => 283,
                'parent_id' => 281,
                'code' => 311119,
                'name' => 'Other Animal Food Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:01',
                'has_child' => 0,
            ),
            283 =>
            array (
                'id' => 284,
                'parent_id' => 279,
                'code' => 3112,
                'name' => 'Grain and Oilseed Milling',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            284 =>
            array (
                'id' => 285,
                'parent_id' => 284,
                'code' => 31121,
                'name' => 'Flour Milling and Malt Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            285 =>
            array (
                'id' => 286,
                'parent_id' => 285,
                'code' => 311211,
                'name' => 'Flour Milling',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:04',
                'has_child' => 0,
            ),
            286 =>
            array (
                'id' => 287,
                'parent_id' => 285,
                'code' => 311212,
                'name' => 'Rice Milling',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:05',
                'has_child' => 0,
            ),
            287 =>
            array (
                'id' => 288,
                'parent_id' => 285,
                'code' => 311213,
                'name' => 'Malt Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:06',
                'has_child' => 0,
            ),
            288 =>
            array (
                'id' => 289,
                'parent_id' => 284,
                'code' => 31122,
                'name' => 'Starch and Vegetable Fats and Oils Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            289 =>
            array (
                'id' => 290,
                'parent_id' => 289,
                'code' => 311221,
                'name' => 'Wet Corn Milling',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:08',
                'has_child' => 0,
            ),
            290 =>
            array (
                'id' => 291,
                'parent_id' => 289,
                'code' => 311224,
                'name' => 'Soybean and Other Oilseed Processing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:09',
                'has_child' => 0,
            ),
            291 =>
            array (
                'id' => 292,
                'parent_id' => 289,
                'code' => 311225,
                'name' => 'Fats and Oils Refining and Blending',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:10',
                'has_child' => 0,
            ),
            292 =>
            array (
                'id' => 293,
                'parent_id' => 284,
                'code' => 31123,
                'name' => 'Breakfast Cereal Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            293 =>
            array (
                'id' => 294,
                'parent_id' => 293,
                'code' => 311230,
                'name' => 'Breakfast Cereal Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:12',
                'has_child' => 0,
            ),
            294 =>
            array (
                'id' => 295,
                'parent_id' => 279,
                'code' => 3113,
                'name' => 'Sugar and Confectionery Product Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            295 =>
            array (
                'id' => 296,
                'parent_id' => 295,
                'code' => 31131,
                'name' => 'Sugar Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            296 =>
            array (
                'id' => 297,
                'parent_id' => 296,
                'code' => 311313,
                'name' => 'Beet Sugar Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:15',
                'has_child' => 0,
            ),
            297 =>
            array (
                'id' => 298,
                'parent_id' => 296,
                'code' => 311314,
                'name' => 'Cane Sugar Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:16',
                'has_child' => 0,
            ),
            298 =>
            array (
                'id' => 299,
                'parent_id' => 295,
                'code' => 31134,
                'name' => 'Nonchocolate Confectionery Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            299 =>
            array (
                'id' => 300,
                'parent_id' => 299,
                'code' => 311340,
                'name' => 'Nonchocolate Confectionery Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:17',
                'has_child' => 0,
            ),
            300 =>
            array (
                'id' => 301,
                'parent_id' => 295,
                'code' => 31135,
                'name' => 'Chocolate and Confectionery Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            301 =>
            array (
                'id' => 302,
                'parent_id' => 301,
                'code' => 311351,
                'name' => 'Chocolate and Confectionery Manufacturing from Cacao Beans',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:19',
                'has_child' => 0,
            ),
            302 =>
            array (
                'id' => 303,
                'parent_id' => 301,
                'code' => 311352,
                'name' => 'Confectionery Manufacturing from Purchased Chocolate',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:20',
                'has_child' => 0,
            ),
            303 =>
            array (
                'id' => 304,
                'parent_id' => 279,
                'code' => 3114,
                'name' => 'Fruit and Vegetable Preserving and Specialty Food Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            304 =>
            array (
                'id' => 305,
                'parent_id' => 304,
                'code' => 31141,
                'name' => 'Frozen Food Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            305 =>
            array (
                'id' => 306,
                'parent_id' => 305,
                'code' => 311411,
                'name' => 'Frozen Fruit, Juice, and Vegetable Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:23',
                'has_child' => 0,
            ),
            306 =>
            array (
                'id' => 307,
                'parent_id' => 305,
                'code' => 311412,
                'name' => 'Frozen Specialty Food Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:24',
                'has_child' => 0,
            ),
            307 =>
            array (
                'id' => 308,
                'parent_id' => 304,
                'code' => 31142,
                'name' => 'Fruit and Vegetable Canning, Pickling, and Drying',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            308 =>
            array (
                'id' => 309,
                'parent_id' => 308,
                'code' => 311421,
                'name' => 'Fruit and Vegetable Canning',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:26',
                'has_child' => 0,
            ),
            309 =>
            array (
                'id' => 310,
                'parent_id' => 308,
                'code' => 311422,
                'name' => 'Specialty Canning',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:27',
                'has_child' => 0,
            ),
            310 =>
            array (
                'id' => 311,
                'parent_id' => 308,
                'code' => 311423,
                'name' => 'Dried and Dehydrated Food Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:28',
                'has_child' => 0,
            ),
            311 =>
            array (
                'id' => 312,
                'parent_id' => 279,
                'code' => 3115,
                'name' => 'Dairy Product Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            312 =>
            array (
                'id' => 313,
                'parent_id' => 312,
                'code' => 31151,
            'name' => 'Dairy Product (except Frozen) Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            313 =>
            array (
                'id' => 314,
                'parent_id' => 313,
                'code' => 311511,
                'name' => 'Fluid Milk Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:31',
                'has_child' => 0,
            ),
            314 =>
            array (
                'id' => 315,
                'parent_id' => 313,
                'code' => 311512,
                'name' => 'Creamery Butter Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:32',
                'has_child' => 0,
            ),
            315 =>
            array (
                'id' => 316,
                'parent_id' => 313,
                'code' => 311513,
                'name' => 'Cheese Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:33',
                'has_child' => 0,
            ),
            316 =>
            array (
                'id' => 317,
                'parent_id' => 313,
                'code' => 311514,
                'name' => 'Dry, Condensed, and Evaporated Dairy Product Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:34',
                'has_child' => 0,
            ),
            317 =>
            array (
                'id' => 318,
                'parent_id' => 312,
                'code' => 31152,
                'name' => 'Ice Cream and Frozen Dessert Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            318 =>
            array (
                'id' => 319,
                'parent_id' => 318,
                'code' => 311520,
                'name' => 'Ice Cream and Frozen Dessert Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:36',
                'has_child' => 0,
            ),
            319 =>
            array (
                'id' => 320,
                'parent_id' => 279,
                'code' => 3116,
                'name' => 'Animal Slaughtering and Processing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            320 =>
            array (
                'id' => 321,
                'parent_id' => 320,
                'code' => 31161,
                'name' => 'Animal Slaughtering and Processing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            321 =>
            array (
                'id' => 322,
                'parent_id' => 321,
                'code' => 311611,
            'name' => 'Animal (except Poultry) Slaughtering',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:39',
                'has_child' => 0,
            ),
            322 =>
            array (
                'id' => 323,
                'parent_id' => 321,
                'code' => 311612,
                'name' => 'Meat Processed from Carcasses',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:40',
                'has_child' => 0,
            ),
            323 =>
            array (
                'id' => 324,
                'parent_id' => 321,
                'code' => 311613,
                'name' => 'Rendering and Meat Byproduct Processing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:41',
                'has_child' => 0,
            ),
            324 =>
            array (
                'id' => 325,
                'parent_id' => 321,
                'code' => 311615,
                'name' => 'Poultry Processing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:42',
                'has_child' => 0,
            ),
            325 =>
            array (
                'id' => 326,
                'parent_id' => 279,
                'code' => 3117,
                'name' => 'Seafood Product Preparation and Packaging',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            326 =>
            array (
                'id' => 327,
                'parent_id' => 326,
                'code' => 31171,
                'name' => 'Seafood Product Preparation and Packaging',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            327 =>
            array (
                'id' => 328,
                'parent_id' => 327,
                'code' => 311710,
                'name' => 'Seafood Product Preparation and Packaging',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:44',
                'has_child' => 0,
            ),
            328 =>
            array (
                'id' => 329,
                'parent_id' => 279,
                'code' => 3118,
                'name' => 'Bakeries and Tortilla Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            329 =>
            array (
                'id' => 330,
                'parent_id' => 329,
                'code' => 31181,
                'name' => 'Bread and Bakery Product Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            330 =>
            array (
                'id' => 331,
                'parent_id' => 330,
                'code' => 311811,
                'name' => 'Retail Bakeries',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:47',
                'has_child' => 0,
            ),
            331 =>
            array (
                'id' => 332,
                'parent_id' => 330,
                'code' => 311812,
                'name' => 'Commercial Bakeries',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:48',
                'has_child' => 0,
            ),
            332 =>
            array (
                'id' => 333,
                'parent_id' => 330,
                'code' => 311813,
                'name' => 'Frozen Cakes, Pies, and Other Pastries Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:49',
                'has_child' => 0,
            ),
            333 =>
            array (
                'id' => 334,
                'parent_id' => 329,
                'code' => 31182,
                'name' => 'Cookie, Cracker, and Pasta Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            334 =>
            array (
                'id' => 335,
                'parent_id' => 334,
                'code' => 311821,
                'name' => 'Cookie and Cracker Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:51',
                'has_child' => 0,
            ),
            335 =>
            array (
                'id' => 336,
                'parent_id' => 334,
                'code' => 311824,
                'name' => 'Dry Pasta, Dough, and Flour Mixes Manufacturing from Purchased Flour',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:52',
                'has_child' => 0,
            ),
            336 =>
            array (
                'id' => 337,
                'parent_id' => 329,
                'code' => 31183,
                'name' => 'Tortilla Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            337 =>
            array (
                'id' => 338,
                'parent_id' => 337,
                'code' => 311830,
                'name' => 'Tortilla Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:54',
                'has_child' => 0,
            ),
            338 =>
            array (
                'id' => 339,
                'parent_id' => 279,
                'code' => 3119,
                'name' => 'Other Food Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            339 =>
            array (
                'id' => 340,
                'parent_id' => 339,
                'code' => 31191,
                'name' => 'Snack Food Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            340 =>
            array (
                'id' => 341,
                'parent_id' => 340,
                'code' => 311911,
                'name' => 'Roasted Nuts and Peanut Butter Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:57',
                'has_child' => 0,
            ),
            341 =>
            array (
                'id' => 342,
                'parent_id' => 340,
                'code' => 311919,
                'name' => 'Other Snack Food Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:51:58',
                'has_child' => 0,
            ),
            342 =>
            array (
                'id' => 343,
                'parent_id' => 339,
                'code' => 31192,
                'name' => 'Coffee and Tea Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            343 =>
            array (
                'id' => 344,
                'parent_id' => 343,
                'code' => 311920,
                'name' => 'Coffee and Tea Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:00',
                'has_child' => 0,
            ),
            344 =>
            array (
                'id' => 345,
                'parent_id' => 339,
                'code' => 31193,
                'name' => 'Flavoring Syrup and Concentrate Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            345 =>
            array (
                'id' => 346,
                'parent_id' => 345,
                'code' => 311930,
                'name' => 'Flavoring Syrup and Concentrate Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:02',
                'has_child' => 0,
            ),
            346 =>
            array (
                'id' => 347,
                'parent_id' => 339,
                'code' => 31194,
                'name' => 'Seasoning and Dressing Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            347 =>
            array (
                'id' => 348,
                'parent_id' => 347,
                'code' => 311941,
                'name' => 'Mayonnaise, Dressing, and Other Prepared Sauce Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:04',
                'has_child' => 0,
            ),
            348 =>
            array (
                'id' => 349,
                'parent_id' => 347,
                'code' => 311942,
                'name' => 'Spice and Extract Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:05',
                'has_child' => 0,
            ),
            349 =>
            array (
                'id' => 350,
                'parent_id' => 339,
                'code' => 31199,
                'name' => 'All Other Food Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            350 =>
            array (
                'id' => 351,
                'parent_id' => 350,
                'code' => 311991,
                'name' => 'Perishable Prepared Food Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:06',
                'has_child' => 0,
            ),
            351 =>
            array (
                'id' => 352,
                'parent_id' => 350,
                'code' => 311999,
                'name' => 'All Other Miscellaneous Food Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:07',
                'has_child' => 0,
            ),
            352 =>
            array (
                'id' => 353,
                'parent_id' => 278,
                'code' => 312,
                'name' => 'Beverage and Tobacco Product Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            353 =>
            array (
                'id' => 354,
                'parent_id' => 353,
                'code' => 3121,
                'name' => 'Beverage Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            354 =>
            array (
                'id' => 355,
                'parent_id' => 354,
                'code' => 31211,
                'name' => 'Soft Drink and Ice Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            355 =>
            array (
                'id' => 356,
                'parent_id' => 355,
                'code' => 312111,
                'name' => 'Soft Drink Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:11',
                'has_child' => 0,
            ),
            356 =>
            array (
                'id' => 357,
                'parent_id' => 355,
                'code' => 312112,
                'name' => 'Bottled Water Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:12',
                'has_child' => 0,
            ),
            357 =>
            array (
                'id' => 358,
                'parent_id' => 355,
                'code' => 312113,
                'name' => 'Ice Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:13',
                'has_child' => 0,
            ),
            358 =>
            array (
                'id' => 359,
                'parent_id' => 354,
                'code' => 31212,
                'name' => 'Breweries',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            359 =>
            array (
                'id' => 360,
                'parent_id' => 359,
                'code' => 312120,
                'name' => 'Breweries',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:15',
                'has_child' => 0,
            ),
            360 =>
            array (
                'id' => 361,
                'parent_id' => 354,
                'code' => 31213,
                'name' => 'Wineries',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            361 =>
            array (
                'id' => 362,
                'parent_id' => 361,
                'code' => 312130,
                'name' => 'Wineries',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:17',
                'has_child' => 0,
            ),
            362 =>
            array (
                'id' => 363,
                'parent_id' => 354,
                'code' => 31214,
                'name' => 'Distilleries',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            363 =>
            array (
                'id' => 364,
                'parent_id' => 363,
                'code' => 312140,
                'name' => 'Distilleries',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:19',
                'has_child' => 0,
            ),
            364 =>
            array (
                'id' => 365,
                'parent_id' => 353,
                'code' => 3122,
                'name' => 'Tobacco Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            365 =>
            array (
                'id' => 366,
                'parent_id' => 365,
                'code' => 31223,
                'name' => 'Tobacco Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            366 =>
            array (
                'id' => 367,
                'parent_id' => 366,
                'code' => 312230,
                'name' => 'Tobacco Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:22',
                'has_child' => 0,
            ),
            367 =>
            array (
                'id' => 368,
                'parent_id' => 278,
                'code' => 313,
                'name' => 'Textile Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            368 =>
            array (
                'id' => 369,
                'parent_id' => 368,
                'code' => 3131,
                'name' => 'Fiber, Yarn, and Thread Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            369 =>
            array (
                'id' => 370,
                'parent_id' => 369,
                'code' => 31311,
                'name' => 'Fiber, Yarn, and Thread Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            370 =>
            array (
                'id' => 371,
                'parent_id' => 370,
                'code' => 313110,
                'name' => 'Fiber, Yarn, and Thread Mills',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:26',
                'has_child' => 0,
            ),
            371 =>
            array (
                'id' => 372,
                'parent_id' => 368,
                'code' => 3132,
                'name' => 'Fabric Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            372 =>
            array (
                'id' => 373,
                'parent_id' => 372,
                'code' => 31321,
                'name' => 'Broadwoven Fabric Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            373 =>
            array (
                'id' => 374,
                'parent_id' => 373,
                'code' => 313210,
                'name' => 'Broadwoven Fabric Mills',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:28',
                'has_child' => 0,
            ),
            374 =>
            array (
                'id' => 375,
                'parent_id' => 372,
                'code' => 31322,
                'name' => 'Narrow Fabric Mills and Schiffli Machine Embroidery',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            375 =>
            array (
                'id' => 376,
                'parent_id' => 375,
                'code' => 313220,
                'name' => 'Narrow Fabric Mills and Schiffli Machine Embroidery',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:30',
                'has_child' => 0,
            ),
            376 =>
            array (
                'id' => 377,
                'parent_id' => 372,
                'code' => 31323,
                'name' => 'Nonwoven Fabric Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            377 =>
            array (
                'id' => 378,
                'parent_id' => 377,
                'code' => 313230,
                'name' => 'Nonwoven Fabric Mills',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:32',
                'has_child' => 0,
            ),
            378 =>
            array (
                'id' => 379,
                'parent_id' => 372,
                'code' => 31324,
                'name' => 'Knit Fabric Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            379 =>
            array (
                'id' => 380,
                'parent_id' => 379,
                'code' => 313240,
                'name' => 'Knit Fabric Mills',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:34',
                'has_child' => 0,
            ),
            380 =>
            array (
                'id' => 381,
                'parent_id' => 368,
                'code' => 3133,
                'name' => 'Textile and Fabric Finishing and Fabric Coating Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            381 =>
            array (
                'id' => 382,
                'parent_id' => 381,
                'code' => 31331,
                'name' => 'Textile and Fabric Finishing Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            382 =>
            array (
                'id' => 383,
                'parent_id' => 382,
                'code' => 313310,
                'name' => 'Textile and Fabric Finishing Mills',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:37',
                'has_child' => 0,
            ),
            383 =>
            array (
                'id' => 384,
                'parent_id' => 381,
                'code' => 31332,
                'name' => 'Fabric Coating Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            384 =>
            array (
                'id' => 385,
                'parent_id' => 384,
                'code' => 313320,
                'name' => 'Fabric Coating Mills',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:39',
                'has_child' => 0,
            ),
            385 =>
            array (
                'id' => 386,
                'parent_id' => 278,
                'code' => 314,
                'name' => 'Textile Product Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            386 =>
            array (
                'id' => 387,
                'parent_id' => 386,
                'code' => 3141,
                'name' => 'Textile Furnishings Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            387 =>
            array (
                'id' => 388,
                'parent_id' => 387,
                'code' => 31411,
                'name' => 'Carpet and Rug Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            388 =>
            array (
                'id' => 389,
                'parent_id' => 388,
                'code' => 314110,
                'name' => 'Carpet and Rug Mills',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:43',
                'has_child' => 0,
            ),
            389 =>
            array (
                'id' => 390,
                'parent_id' => 387,
                'code' => 31412,
                'name' => 'Curtain and Linen Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            390 =>
            array (
                'id' => 391,
                'parent_id' => 390,
                'code' => 314120,
                'name' => 'Curtain and Linen Mills',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:45',
                'has_child' => 0,
            ),
            391 =>
            array (
                'id' => 392,
                'parent_id' => 386,
                'code' => 3149,
                'name' => 'Other Textile Product Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            392 =>
            array (
                'id' => 393,
                'parent_id' => 392,
                'code' => 31491,
                'name' => 'Textile Bag and Canvas Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            393 =>
            array (
                'id' => 394,
                'parent_id' => 393,
                'code' => 314910,
                'name' => 'Textile Bag and Canvas Mills',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:48',
                'has_child' => 0,
            ),
            394 =>
            array (
                'id' => 395,
                'parent_id' => 392,
                'code' => 31499,
                'name' => 'All Other Textile Product Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            395 =>
            array (
                'id' => 396,
                'parent_id' => 395,
                'code' => 314994,
                'name' => 'Rope, Cordage, Twine, Tire Cord, and Tire Fabric Mills',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:50',
                'has_child' => 0,
            ),
            396 =>
            array (
                'id' => 397,
                'parent_id' => 395,
                'code' => 314999,
                'name' => 'All Other Miscellaneous Textile Product Mills',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:50',
                'has_child' => 0,
            ),
            397 =>
            array (
                'id' => 398,
                'parent_id' => 278,
                'code' => 315,
                'name' => 'Apparel Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            398 =>
            array (
                'id' => 399,
                'parent_id' => 398,
                'code' => 3151,
                'name' => 'Apparel Knitting Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            399 =>
            array (
                'id' => 400,
                'parent_id' => 399,
                'code' => 31511,
                'name' => 'Hosiery and Sock Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            400 =>
            array (
                'id' => 401,
                'parent_id' => 400,
                'code' => 315110,
                'name' => 'Hosiery and Sock Mills',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:54',
                'has_child' => 0,
            ),
            401 =>
            array (
                'id' => 402,
                'parent_id' => 399,
                'code' => 31519,
                'name' => 'Other Apparel Knitting Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            402 =>
            array (
                'id' => 403,
                'parent_id' => 402,
                'code' => 315190,
                'name' => 'Other Apparel Knitting Mills',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:56',
                'has_child' => 0,
            ),
            403 =>
            array (
                'id' => 404,
                'parent_id' => 398,
                'code' => 3152,
                'name' => 'Cut and Sew Apparel Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            404 =>
            array (
                'id' => 405,
                'parent_id' => 404,
                'code' => 31521,
                'name' => 'Cut and Sew Apparel Contractors',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            405 =>
            array (
                'id' => 406,
                'parent_id' => 405,
                'code' => 315210,
                'name' => 'Cut and Sew Apparel Contractors',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:52:59',
                'has_child' => 0,
            ),
            406 =>
            array (
                'id' => 407,
                'parent_id' => 404,
                'code' => 31522,
                'name' => 'Men’s and Boys’ Cut and Sew Apparel Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            407 =>
            array (
                'id' => 408,
                'parent_id' => 407,
                'code' => 315220,
                'name' => 'Men’s and Boys’ Cut and Sew Apparel Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:01',
                'has_child' => 0,
            ),
            408 =>
            array (
                'id' => 409,
                'parent_id' => 404,
                'code' => 31524,
                'name' => 'Women’s, Girls’, and Infants’ Cut and Sew Apparel Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            409 =>
            array (
                'id' => 410,
                'parent_id' => 409,
                'code' => 315240,
                'name' => 'Women’s, Girls’, and Infants’ Cut and Sew Apparel Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:03',
                'has_child' => 0,
            ),
            410 =>
            array (
                'id' => 411,
                'parent_id' => 404,
                'code' => 31528,
                'name' => 'Other Cut and Sew Apparel Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            411 =>
            array (
                'id' => 412,
                'parent_id' => 411,
                'code' => 315280,
                'name' => 'Other Cut and Sew Apparel Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:05',
                'has_child' => 0,
            ),
            412 =>
            array (
                'id' => 413,
                'parent_id' => 398,
                'code' => 3159,
                'name' => 'Apparel Accessories and Other Apparel Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            413 =>
            array (
                'id' => 414,
                'parent_id' => 413,
                'code' => 31599,
                'name' => 'Apparel Accessories and Other Apparel Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            414 =>
            array (
                'id' => 415,
                'parent_id' => 414,
                'code' => 315990,
                'name' => 'Apparel Accessories and Other Apparel Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:08',
                'has_child' => 0,
            ),
            415 =>
            array (
                'id' => 416,
                'parent_id' => 278,
                'code' => 316,
                'name' => 'Leather and Allied Product Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            416 =>
            array (
                'id' => 417,
                'parent_id' => 416,
                'code' => 3161,
                'name' => 'Leather and Hide Tanning and Finishing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            417 =>
            array (
                'id' => 418,
                'parent_id' => 417,
                'code' => 31611,
                'name' => 'Leather and Hide Tanning and Finishing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            418 =>
            array (
                'id' => 419,
                'parent_id' => 418,
                'code' => 316110,
                'name' => 'Leather and Hide Tanning and Finishing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:12',
                'has_child' => 0,
            ),
            419 =>
            array (
                'id' => 420,
                'parent_id' => 416,
                'code' => 3162,
                'name' => 'Footwear Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            420 =>
            array (
                'id' => 421,
                'parent_id' => 420,
                'code' => 31621,
                'name' => 'Footwear Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            421 =>
            array (
                'id' => 422,
                'parent_id' => 421,
                'code' => 316210,
                'name' => 'Footwear Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:14',
                'has_child' => 0,
            ),
            422 =>
            array (
                'id' => 423,
                'parent_id' => 416,
                'code' => 3169,
                'name' => 'Other Leather and Allied Product Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            423 =>
            array (
                'id' => 424,
                'parent_id' => 423,
                'code' => 31699,
                'name' => 'Other Leather and Allied Product Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            424 =>
            array (
                'id' => 425,
                'parent_id' => 424,
                'code' => 316992,
                'name' => 'Women\'s Handbag and Purse Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:17',
                'has_child' => 0,
            ),
            425 =>
            array (
                'id' => 426,
                'parent_id' => 424,
                'code' => 316998,
                'name' => 'All Other Leather Good and Allied Product Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:18',
                'has_child' => 0,
            ),
            426 =>
            array (
                'id' => 427,
                'parent_id' => NULL,
                'code' => 321,
                'name' => 'Wood Product Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            427 =>
            array (
                'id' => 428,
                'parent_id' => 427,
                'code' => 3211,
                'name' => 'Sawmills and Wood Preservation',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            428 =>
            array (
                'id' => 429,
                'parent_id' => 428,
                'code' => 32111,
                'name' => 'Sawmills and Wood Preservation',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            429 =>
            array (
                'id' => 430,
                'parent_id' => 429,
                'code' => 321113,
                'name' => 'Sawmills',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:22',
                'has_child' => 0,
            ),
            430 =>
            array (
                'id' => 431,
                'parent_id' => 429,
                'code' => 321114,
                'name' => 'Wood Preservation',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:23',
                'has_child' => 0,
            ),
            431 =>
            array (
                'id' => 432,
                'parent_id' => 427,
                'code' => 3212,
                'name' => 'Veneer, Plywood, and Engineered Wood Product Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            432 =>
            array (
                'id' => 433,
                'parent_id' => 432,
                'code' => 32121,
                'name' => 'Veneer, Plywood, and Engineered Wood Product Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            433 =>
            array (
                'id' => 434,
                'parent_id' => 433,
                'code' => 321211,
                'name' => 'Hardwood Veneer and Plywood Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:26',
                'has_child' => 0,
            ),
            434 =>
            array (
                'id' => 435,
                'parent_id' => 433,
                'code' => 321212,
                'name' => 'Softwood Veneer and Plywood Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:27',
                'has_child' => 0,
            ),
            435 =>
            array (
                'id' => 436,
                'parent_id' => 433,
                'code' => 321213,
            'name' => 'Engineered Wood Member (except Truss) Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:28',
                'has_child' => 0,
            ),
            436 =>
            array (
                'id' => 437,
                'parent_id' => 433,
                'code' => 321214,
                'name' => 'Truss Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:29',
                'has_child' => 0,
            ),
            437 =>
            array (
                'id' => 438,
                'parent_id' => 433,
                'code' => 321219,
                'name' => 'Reconstituted Wood Product Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:30',
                'has_child' => 0,
            ),
            438 =>
            array (
                'id' => 439,
                'parent_id' => 427,
                'code' => 3219,
                'name' => 'Other Wood Product Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            439 =>
            array (
                'id' => 440,
                'parent_id' => 439,
                'code' => 32191,
                'name' => 'Millwork',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            440 =>
            array (
                'id' => 441,
                'parent_id' => 440,
                'code' => 321911,
                'name' => 'Wood Window and Door Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:33',
                'has_child' => 0,
            ),
            441 =>
            array (
                'id' => 442,
                'parent_id' => 440,
                'code' => 321912,
                'name' => 'Cut Stock, Resawing Lumber, and Planing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:34',
                'has_child' => 0,
            ),
            442 =>
            array (
                'id' => 443,
                'parent_id' => 440,
                'code' => 321918,
            'name' => 'Other Millwork (including Flooring)',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:35',
                'has_child' => 0,
            ),
            443 =>
            array (
                'id' => 444,
                'parent_id' => 439,
                'code' => 32192,
                'name' => 'Wood Container and Pallet Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            444 =>
            array (
                'id' => 445,
                'parent_id' => 444,
                'code' => 321920,
                'name' => 'Wood Container and Pallet Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:36',
                'has_child' => 0,
            ),
            445 =>
            array (
                'id' => 446,
                'parent_id' => 439,
                'code' => 32199,
                'name' => 'All Other Wood Product Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            446 =>
            array (
                'id' => 447,
                'parent_id' => 446,
                'code' => 321991,
            'name' => 'Manufactured Home (Mobile Home) Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:38',
                'has_child' => 0,
            ),
            447 =>
            array (
                'id' => 448,
                'parent_id' => 446,
                'code' => 321992,
                'name' => 'Prefabricated Wood Building Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:39',
                'has_child' => 0,
            ),
            448 =>
            array (
                'id' => 449,
                'parent_id' => 446,
                'code' => 321999,
                'name' => 'All Other Miscellaneous Wood Product Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:40',
                'has_child' => 0,
            ),
            449 =>
            array (
                'id' => 450,
                'parent_id' => NULL,
                'code' => 322,
                'name' => 'Paper Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            450 =>
            array (
                'id' => 451,
                'parent_id' => 450,
                'code' => 3221,
                'name' => 'Pulp, Paper, and Paperboard Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            451 =>
            array (
                'id' => 452,
                'parent_id' => 451,
                'code' => 32211,
                'name' => 'Pulp Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            452 =>
            array (
                'id' => 453,
                'parent_id' => 452,
                'code' => 322110,
                'name' => 'Pulp Mills',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:44',
                'has_child' => 0,
            ),
            453 =>
            array (
                'id' => 454,
                'parent_id' => 451,
                'code' => 32212,
                'name' => 'Paper Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            454 =>
            array (
                'id' => 455,
                'parent_id' => 454,
                'code' => 322121,
            'name' => 'Paper (except Newsprint) Mills',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:46',
                'has_child' => 0,
            ),
            455 =>
            array (
                'id' => 456,
                'parent_id' => 454,
                'code' => 322122,
                'name' => 'Newsprint Mills',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:47',
                'has_child' => 0,
            ),
            456 =>
            array (
                'id' => 457,
                'parent_id' => 451,
                'code' => 32213,
                'name' => 'Paperboard Mills',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            457 =>
            array (
                'id' => 458,
                'parent_id' => 457,
                'code' => 322130,
                'name' => 'Paperboard Mills',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:49',
                'has_child' => 0,
            ),
            458 =>
            array (
                'id' => 459,
                'parent_id' => 450,
                'code' => 3222,
                'name' => 'Converted Paper Product Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            459 =>
            array (
                'id' => 460,
                'parent_id' => 459,
                'code' => 32221,
                'name' => 'Paperboard Container Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            460 =>
            array (
                'id' => 461,
                'parent_id' => 460,
                'code' => 322211,
                'name' => 'Corrugated and Solid Fiber Box Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:52',
                'has_child' => 0,
            ),
            461 =>
            array (
                'id' => 462,
                'parent_id' => 460,
                'code' => 322212,
                'name' => 'Folding Paperboard Box Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:53',
                'has_child' => 0,
            ),
            462 =>
            array (
                'id' => 463,
                'parent_id' => 460,
                'code' => 322219,
                'name' => 'Other Paperboard Container Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:54',
                'has_child' => 0,
            ),
            463 =>
            array (
                'id' => 464,
                'parent_id' => 459,
                'code' => 32222,
                'name' => 'Paper Bag and Coated and Treated Paper Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            464 =>
            array (
                'id' => 465,
                'parent_id' => 464,
                'code' => 322220,
                'name' => 'Paper Bag and Coated and Treated Paper Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:56',
                'has_child' => 0,
            ),
            465 =>
            array (
                'id' => 466,
                'parent_id' => 459,
                'code' => 32223,
                'name' => 'Stationery Product Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            466 =>
            array (
                'id' => 467,
                'parent_id' => 466,
                'code' => 322230,
                'name' => 'Stationery Product Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:58',
                'has_child' => 0,
            ),
            467 =>
            array (
                'id' => 468,
                'parent_id' => 459,
                'code' => 32229,
                'name' => 'Other Converted Paper Product Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            468 =>
            array (
                'id' => 469,
                'parent_id' => 468,
                'code' => 322291,
                'name' => 'Sanitary Paper Product Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:53:59',
                'has_child' => 0,
            ),
            469 =>
            array (
                'id' => 470,
                'parent_id' => 468,
                'code' => 322299,
                'name' => 'All Other Converted Paper Product Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:54:00',
                'has_child' => 0,
            ),
            470 =>
            array (
                'id' => 471,
                'parent_id' => NULL,
                'code' => 323,
                'name' => 'Printing and Related Support Activities',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            471 =>
            array (
                'id' => 472,
                'parent_id' => 471,
                'code' => 3231,
                'name' => 'Printing and Related Support Activities',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            472 =>
            array (
                'id' => 473,
                'parent_id' => 472,
                'code' => 32311,
                'name' => 'Printing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            473 =>
            array (
                'id' => 474,
                'parent_id' => 473,
                'code' => 323111,
            'name' => 'Commercial Printing (except Screen and Books)',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:54:04',
                'has_child' => 0,
            ),
            474 =>
            array (
                'id' => 475,
                'parent_id' => 473,
                'code' => 323113,
                'name' => 'Commercial Screen Printing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:54:05',
                'has_child' => 0,
            ),
            475 =>
            array (
                'id' => 476,
                'parent_id' => 473,
                'code' => 323117,
                'name' => 'Books Printing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:54:06',
                'has_child' => 0,
            ),
            476 =>
            array (
                'id' => 477,
                'parent_id' => 472,
                'code' => 32312,
                'name' => 'Support Activities for Printing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            477 =>
            array (
                'id' => 478,
                'parent_id' => 477,
                'code' => 323120,
                'name' => 'Support Activities for Printing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:54:08',
                'has_child' => 0,
            ),
            478 =>
            array (
                'id' => 479,
                'parent_id' => NULL,
                'code' => 324,
                'name' => 'Petroleum and Coal Products Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            479 =>
            array (
                'id' => 480,
                'parent_id' => 479,
                'code' => 3241,
                'name' => 'Petroleum and Coal Products Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            480 =>
            array (
                'id' => 481,
                'parent_id' => 480,
                'code' => 32411,
                'name' => 'Petroleum Refineries',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            481 =>
            array (
                'id' => 482,
                'parent_id' => 481,
                'code' => 324110,
                'name' => 'Petroleum Refineries',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:54:12',
                'has_child' => 0,
            ),
            482 =>
            array (
                'id' => 483,
                'parent_id' => 480,
                'code' => 32412,
                'name' => 'Asphalt Paving, Roofing, and Saturated Materials Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            483 =>
            array (
                'id' => 484,
                'parent_id' => 483,
                'code' => 324121,
                'name' => 'Asphalt Paving Mixture and Block Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:54:14',
                'has_child' => 0,
            ),
            484 =>
            array (
                'id' => 485,
                'parent_id' => 483,
                'code' => 324122,
                'name' => 'Asphalt Shingle and Coating Materials Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:54:15',
                'has_child' => 0,
            ),
            485 =>
            array (
                'id' => 486,
                'parent_id' => 480,
                'code' => 32419,
                'name' => 'Other Petroleum and Coal Products Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            486 =>
            array (
                'id' => 487,
                'parent_id' => 486,
                'code' => 324191,
                'name' => 'Petroleum Lubricating Oil and Grease Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:54:17',
                'has_child' => 0,
            ),
            487 =>
            array (
                'id' => 488,
                'parent_id' => 486,
                'code' => 324199,
                'name' => 'All Other Petroleum and Coal Products Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:54:18',
                'has_child' => 0,
            ),
            488 =>
            array (
                'id' => 489,
                'parent_id' => NULL,
                'code' => 325,
                'name' => 'Chemical Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            489 =>
            array (
                'id' => 490,
                'parent_id' => 489,
                'code' => 3251,
                'name' => 'Basic Chemical Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            490 =>
            array (
                'id' => 491,
                'parent_id' => 490,
                'code' => 32511,
                'name' => 'Petrochemical Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            491 =>
            array (
                'id' => 492,
                'parent_id' => 491,
                'code' => 325110,
                'name' => 'Petrochemical Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:54:21',
                'has_child' => 0,
            ),
            492 =>
            array (
                'id' => 493,
                'parent_id' => 490,
                'code' => 32512,
                'name' => 'Industrial Gas Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            493 =>
            array (
                'id' => 494,
                'parent_id' => 493,
                'code' => 325120,
                'name' => 'Industrial Gas Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:54:23',
                'has_child' => 0,
            ),
            494 =>
            array (
                'id' => 495,
                'parent_id' => 490,
                'code' => 32513,
                'name' => 'Synthetic Dye and Pigment Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            495 =>
            array (
                'id' => 496,
                'parent_id' => 495,
                'code' => 325130,
                'name' => 'Synthetic Dye and Pigment Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:54:25',
                'has_child' => 0,
            ),
            496 =>
            array (
                'id' => 497,
                'parent_id' => 490,
                'code' => 32518,
                'name' => 'Other Basic Inorganic Chemical Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            497 =>
            array (
                'id' => 498,
                'parent_id' => 497,
                'code' => 325180,
                'name' => 'Other Basic Inorganic Chemical Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:54:27',
                'has_child' => 0,
            ),
            498 =>
            array (
                'id' => 499,
                'parent_id' => 490,
                'code' => 32519,
                'name' => 'Other Basic Organic Chemical Manufacturing',
                'created_at' => NULL,
                'updated_at' => NULL,
                'has_child' => 1,
            ),
            499 =>
            array (
                'id' => 500,
                'parent_id' => 499,
                'code' => 325193,
                'name' => 'Ethyl Alcohol Manufacturing',
                'created_at' => NULL,
                'updated_at' => '2019-04-08 18:54:29',
                'has_child' => 0,
            ),
        ));


    }
}
