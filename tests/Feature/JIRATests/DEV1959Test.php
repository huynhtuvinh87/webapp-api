<?php

namespace Tests\Feature;

use App\Lib\Services\HiringOrganizationComplianceV2;
use App\Models\Contractor;
use App\Models\HiringOrganization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DEV1959Test extends TestCase
{
    /**
     * kwselectric@gmail.com
     * @testdox KWS Electric should have an employee compliance level of 100%
     *
     * @group DEV1959
     */
    public function testDashboardComplianceLevelForKwsElectric()
    {
        // Entegrus
        $hiringOrg = HiringOrganization::find(34);
        $contractor = Contractor::find(1183);

        $contractorsComplianceQueryRaw = HiringOrganizationComplianceV2::getContractorsWithComplianceQuery($hiringOrg);
        $contractorsComplianceQuery = DB::table(DB::raw("({$contractorsComplianceQueryRaw->toSql()}) as tbl"))
            ->where('contractor_id', DB::raw($contractor->id));

        Storage::drive('scripts')->put(__FUNCTION__ . '.sql', $contractorsComplianceQuery->toSql());

        $contractorsComplianceRes = $contractorsComplianceQuery->get();

        $this->assertCount(1, $contractorsComplianceRes, "Should be able to find contractor for hiring org");

        $contractorWithCompliance = $contractorsComplianceRes->first();

        $this->assertEquals(100, $contractorWithCompliance->contractor_compliance);
        $this->assertEquals(100, $contractorWithCompliance->employee_compliance);
    }

    /**
     * Greentreeontario@gmail.com
     * @testdox Green Tree Ontario should have 100% employee and corporate compliance
     *
     * @group DEV1959
     */
    public function testDashboardComplianceForGreenTreeOntario()
    {
        $hiringOrg = HiringOrganization::find(34);
        $contractor = Contractor::find(1585);

        $contractorsComplianceQueryRaw = HiringOrganizationComplianceV2::getContractorsWithComplianceQuery($hiringOrg);
        $contractorsComplianceQuery = DB::table(DB::raw("({$contractorsComplianceQueryRaw->toSql()}) as tbl"))
            ->where('contractor_id', DB::raw($contractor->id));

        Storage::drive('scripts')->put(__FUNCTION__ . '.sql', $contractorsComplianceQuery->toSql());

        $contractorsComplianceRes = $contractorsComplianceQuery->get();

        $this->assertCount(1, $contractorsComplianceRes, "Should be able to find contractor for hiring org");

        $contractorWithCompliance = $contractorsComplianceRes->first();

        $this->assertEquals(100, $contractorWithCompliance->contractor_compliance);
        $this->assertEquals(100, $contractorWithCompliance->employee_compliance);
    }
}
