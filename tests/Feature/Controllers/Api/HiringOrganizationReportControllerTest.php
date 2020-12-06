<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\HiringOrganizationReportController;
use App\Models\Contractor;
use App\Models\HiringOrganization;
use App\Models\Position;
use App\Models\Requirement;
use Tests\TestCase;

class HiringOrganizationReportControllerTest extends TestCase
{
    public static $reportController = null;

    public static $hiringOrg = null;
    public static $contractor = null;

    public function setUp(): void
    {
        parent::setUp();
        static::$reportController = new HiringOrganizationReportController();
        $this->initOrgs();
    }

    public function initOrgs()
    {
        // Creating Hiring Org
        static::$hiringOrg = factory(HiringOrganization::class)->create([

        ]);

        // Creating Contractor
        static::$contractor = factory(Contractor::class)->create([

        ]);

        // Attaching
        static::$hiringOrg->contractors()->sync(static::$contractor);
    }

    /**
     * @group Report
     */
    public function testPendingInternalRequirements()
    {
        $hiringOrg = static::$hiringOrg;

        // Creating a position with an internal requirement, and assigning it to contractor
        $position = factory(Position::class)->create([
            'hiring_organization_id' => static::$hiringOrg->id,
            'position_type' => 'contractor',
            'is_active' => true,

            'name' => __FUNCTION__
        ]);
        $requirement = factory(Requirement::class)->create([
            'hiring_organization_id' => static::$hiringOrg->id,
            'type' => 'internal_document'
        ]);
        $position->requirements()->attach($requirement);
        static::$contractor->positions()->attach($position);

        // Getting report
        $reportQuery = static::$reportController->pendingInternalRequirements($hiringOrg);
        $reportRes = $reportQuery->get();

        $this->assertEquals(1, $reportRes->count());
    }
}
