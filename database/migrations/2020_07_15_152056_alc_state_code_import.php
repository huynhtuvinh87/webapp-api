<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Facades\Excel;

class AlcStateCodeImport extends Migration
{
    protected $newTableName = "alc_district_state_codes";
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createTable();
        $this->importDistrictStates();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->newTableName);
    }

    public function createTable()
    {
        try {
            DB::beginTransaction();

            Schema::dropIfExists($this->newTableName);

            Schema::create($this->newTableName, function (Blueprint $table) {
                $table->increments('id');
                $table->string('district');
                $table->string('code');
                $table->string('state');
            });

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            Log::error("Failed to migrate " . __FILE__);

            // if (config('app.env') != 'development') {
            throw new Exception($e->getMessage());
            // }
        }
    }

    public function importDistrictStates()
    {
        // NOTE: Confirm naming
        // Confirm if we want to use north california vs south california for agency code
        // Get rid of North / South designation for california
        Excel::import(new ALCDistrictStateCodeImport($this), storage_path('alc/alc_state_codes.csv'));
    }
}
class ALCDistrictStateCodeImport implements ToCollection
{

    public function collection(Collection $rows)
    {
        Log::info("Starting import");
        $insertArr = [];

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex == 0) {
                // Verify column headers

                $checks = [
                    $row[0] == 'AccountName',
                    $row[1] == 'AgencyCode',
                    $row[2] == 'StateName',
                ];

                $failCount = collect($checks)
                    ->filter(function ($check) {return $check != true;})
                    ->count();

                if ($failCount > 0) {
                    throw new Exception("Incorrect column headers for import");
                }

            } else {

                $district = $row[0];
                $code = $row[1];
                $state = $row[2];

                if($state == 'CA - South' || $state == 'CA - North'){
                    $state = 'CA';
                }

                $insertArr[] = [
                    'district' => $district,
                    'code' => $code,
                    'state' => $state,
                ];

            }
        }

        DB::table('alc_district_state_codes')
            ->insert($insertArr);

        Log::info("Finished import");
    }
}
