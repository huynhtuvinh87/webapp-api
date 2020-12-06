<?php

namespace Tests\Feature;

use App\Jobs\ALCReports;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Log;

class ALCReportsTest extends TestCase
{
    // use DatabaseTransactions;
    // use RefreshDatabase;
    // use DatabaseMigrations;

    // SETUP & CLEAN UPS
    public static $isInit = false;
    public static $reportJob = null;

    public function setUp(): void
    {
        parent::setUp();
        if (!static::$isInit) {
            static::$reportJob = new ALCReports();
        }
    }

    /**
     * @group ALC_CSV
     * @group Live
     *
     */
    public function testAllExport()
    {
        static::$reportJob->export();
    }

    /**
     * This method is to test access to ALC's SFTP server from the current environment
     * @group ALC_CSV
     * @group Live
     */
    public function testSftp()
    {
        $disk = 'alc_sftp';

        $filePath = "testfile.txt";
        $fileContents = Carbon::now() . ": This is a test upload";

        // Putting file on server
        Storage::disk($disk)
            ->put($filePath, $fileContents);

        // Testing that the file exists on the server now
        $fileExists = Storage::disk($disk)->exists($filePath);
        $this->assertEquals(true, $fileExists, "File was not uploaded to the server properly");

        // Reading File
        $file = Storage::disk($disk)->get($filePath);

        // Assertions
        $this->assertNotNull($file);
        $this->assertEquals($fileContents, $file);

        // Storage::disk($disk)->delete($filePath);
        // $this->assertEquals(false, Storage::disk($disk)->exists($filePath), "File should be removed properly");
    }

    public function disks(){
        return [
            [
                'disk' => 'local'
            ]
        ];
    }

    /**
     * @group ALC_CSV
     * @dataProvider disks
     */
    public function testQueryToCSV($disk)
    {
        $query = DB::table("countries")->limit(15);
        $fileName = "countriesTable.csv";

        static::$reportJob->queryToCSV($query, $fileName, $disk);

        // Checking the file exists on specified disk
        $exists = Storage::disk($disk)->exists($fileName);
        $this->assertEquals(true, $exists, "File should exist after building");

        // Checking contents
        $queryRes = $query->get();
        $fileContents = Storage::disk($disk)->get($fileName);

        // Columns
        $expectedColumns = array_keys(get_object_vars($queryRes->first()));
        $fileColumns = explode(",", explode("\n", $fileContents)[0]);

        $this->assertEquals($expectedColumns, $fileColumns);

        // Records
        $expectedRowCount = $queryRes->count();
        $fileRowCount = sizeof(explode("\n", $fileContents)) - 2; // -2 for first and last row

        $this->assertEquals($expectedRowCount, $fileRowCount);

        // Cleanup
        Storage::disk($disk)->delete($fileName);
        $this->assertEquals(false, Storage::disk($disk)->exists($fileName), "File should be removed properly");
    }

    /**
     * @group ALC_CSV
     * @dataProvider disks
     */
    public function testCreateServiceProviderComplianceCsv($disk)
    {
        $fileName = __FUNCTION__;
        $fileName = "sp_compliance";

        $res = static::$reportJob->createServiceProviderComplianceCsv($fileName, $disk);
        $this->assertEquals(true, $res);

        // Checking file exists
        $exists = Storage::disk($disk)->exists($fileName . '.csv');
        $this->assertEquals(true, $exists, "File $fileName should exist on $disk");

        // Cleanup
        // Storage::disk($disk)->delete($fileName);
        // $this->assertEquals(false, Storage::disk($disk)->exists($fileName), "File should be removed properly");
    }

    /**
     * @group ALC_CSV
     * @dataProvider disks
     */
    public function testCreateServiceProviderEmployeeDistrictComplianceCsv($disk)
    {
        $fileName = __FUNCTION__;
        $fileName = "sp_employee_compliance";

        $res = static::$reportJob->createServiceProviderEmployeeDistrictComplianceCsv($fileName, $disk);
        $this->assertEquals(true, $res);

        // Checking file exists
        $exists = Storage::disk($disk)->exists($fileName . '.csv');
        $this->assertEquals(true, $exists, "File $fileName should exist on $disk");

        // Cleanup
        // Storage::disk($disk)->delete($fileName);
        // $this->assertEquals(false, Storage::disk($disk)->exists($fileName), "File should be removed properly");
    }

    /**
     * @group ALC_CSV
     * @dataProvider disks
     */
    public function testCreateDriverInformationCsv($disk)
    {
        $fileName = __FUNCTION__;
        $fileName = "driver_information";

        $res = static::$reportJob->createDriverInformationCsv($fileName, $disk);
        $this->assertEquals(true, $res);

        // Checking file exists
        $exists = Storage::disk($disk)->exists($fileName . '.csv');
        $this->assertEquals(true, $exists, "File $fileName should exist on $disk");

        // Cleanup
        // Storage::disk($disk)->delete($fileName);
        // $this->assertEquals(false, Storage::disk($disk)->exists($fileName), "File should be removed properly");
    }

    /**
     * @group ALC_CSV
     * @dataProvider disks
     */
    public function testCreateInternalDriverInformationCsv($disk)
    {
        $fileName = __FUNCTION__;
        $fileName = "internal_driver_information";

        $res = static::$reportJob->createInternalDriverInformationCsv($fileName, $disk);
        $this->assertEquals(true, $res);

        // Checking file exists
        $exists = Storage::disk($disk)->exists($fileName . '.csv');
        $this->assertEquals(true, $exists, "File $fileName should exist on $disk");

        // Cleanup
        // Storage::disk($disk)->delete($fileName);
        // $this->assertEquals(false, Storage::disk($disk)->exists($fileName), "File should be removed properly");
    }


    /**
     * @group ALC_CSV
     * @dataProvider disks
     */
    public function testCreateInternalServiceProviderInformationCsv($disk)
    {
        $fileName = __FUNCTION__;
        $fileName = "internal_service_provider_information";

        $res =static::$reportJob->createInternalServiceProviderInformationCsv($fileName, $disk);
        $this->assertEquals(true, $res);

        // Checking file exists
        $exists = Storage::disk($disk)->exists($fileName . '.csv');
        $this->assertEquals(true, $exists, "File $fileName should exist on $disk");

        // Cleanup
        // Storage::disk($disk)->delete($fileName);
        // $this->assertEquals(false, Storage::disk($disk)->exists($fileName), "File should be removed properly");
    }

    /**
     * @group ALC_CSV
     * @dataProvider disks
     */
    public function testCreateServiceProviderInformationCsv($disk)
    {
        $fileName = __FUNCTION__;
        $fileName = "service_provider_information";

        $res = static::$reportJob->createServiceProviderInformationCsv($fileName, $disk);
        $this->assertEquals(true, $res);

        // Checking file exists
        $exists = Storage::disk($disk)->exists($fileName . '.csv');
        $this->assertEquals(true, $exists, "File $fileName should exist on $disk");

        // Cleanup
        // Storage::disk($disk)->delete($fileName);
        // $this->assertEquals(false, Storage::disk($disk)->exists($fileName), "File should be removed properly");
    }

    /**
     * @group ALC_CSV
     * @dataProvider disks
     */
    public function testCreateVehicleInformationCsv($disk)
    {
        $fileName = __FUNCTION__;
        $fileName = "vehicle_information";

        $res = static::$reportJob->createVehicleInformationCsv($fileName, $disk);
        $this->assertEquals(true, $res);

        // Checking file exists
        $exists = Storage::disk($disk)->exists($fileName . '.csv');
        $this->assertEquals(true, $exists, "File $fileName should exist on $disk");

        // Cleanup
        // Storage::disk($disk)->delete($fileName);
        // $this->assertEquals(false, Storage::disk($disk)->exists($fileName), "File should be removed properly");
    }

    /**
     * @group ALC_CSV
     * @dataProvider disks
     */
    public function testCreateInternalStudentMonitorInformationCsv($disk)
    {
        $fileName = __FUNCTION__;
        $fileName = "internal_student_monitor_information";

        $res = static::$reportJob->createInternalStudentMonitorInformationCsv($fileName, $disk);
        $this->assertEquals(true, $res);

        // Checking file exists
        $exists = Storage::disk($disk)->exists($fileName . '.csv');
        $this->assertEquals(true, $exists, "File $fileName should exist on $disk");

        // Cleanup
        // Storage::disk($disk)->delete($fileName);
        // $this->assertEquals(false, Storage::disk($disk)->exists($fileName), "File should be removed properly");
    }


    /**
     * @group ALC_CSV
     * @dataProvider disks
     */
    public function testCreateStudentMonitorInformationCsv($disk)
    {
        $fileName = __FUNCTION__;
        $fileName = "student_monitor_information";

        $res = static::$reportJob->createStudentMonitorInformationCsv($fileName, $disk);
        $this->assertEquals(true, $res);

        // Checking file exists
        $exists = Storage::disk($disk)->exists($fileName . '.csv');
        $this->assertEquals(true, $exists, "File $fileName should exist on $disk");

        // Cleanup
        // Storage::disk($disk)->delete($fileName);
        // $this->assertEquals(false, Storage::disk($disk)->exists($fileName), "File should be removed properly");
    }


    /* ---------------------------- Additional Tests ---------------------------- */

    /**
     * This test checks to see that if a resource is reassigned, the information follows the employee
     * For the vehicle_information.csv
     *
     * NOTE: Marking as debug for attention
     */
    public function testVehicleInformationFollowsAssignee()
    {
        $this->markTestIncomplete("Need to write");
    }

}
