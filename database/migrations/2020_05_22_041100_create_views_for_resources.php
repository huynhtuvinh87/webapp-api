<?php

use App\Lib\Services\ResourceCompliance;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateViewsForResources extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP VIEW IF EXISTS view_contractor_resource_compliance_by_hiring_org');
        DB::statement("CREATE VIEW view_contractor_resource_compliance_by_hiring_org as {$this->createViewContractorResourceComplianceByHiringOrgQuery()}");

        DB::statement('DROP VIEW IF EXISTS view_contractor_resource_compliance_by_hiring_org_position');
        DB::statement("CREATE VIEW view_contractor_resource_compliance_by_hiring_org_position as {$this->createViewContractorResourceComplianceByHiringOrgPositionQuery()}");

        DB::statement("DROP VIEW IF EXISTS view_contractor_resource_overall_compliance");
        DB::statement("CREATE VIEW view_contractor_resource_overall_compliance as {$this->createViewContractorResourceOverallComplianceQuery()}");

        DB::statement("DROP VIEW IF EXISTS view_contractor_resource_position_requirements");
        DB::statement("CREATE VIEW view_contractor_resource_position_requirements as {$this->createViewContractorResourcePositionRequirementsQuery()}");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('view_contractor_resource_compliance_by_hiring_org');
        Schema::dropIfExists('view_contractor_resource_compliance_by_hiring_org_position');
        Schema::dropIfExists('view_contractor_resource_overall_compliance');
        Schema::dropIfExists('view_contractor_resource_position_requirements');

    }

    public function createViewContractorResourceComplianceByHiringOrgQuery()
    {
        return ResourceCompliance::createViewContractorResourceComplianceByHiringOrgQuery();
    }

    public function createViewContractorResourceComplianceByHiringOrgPositionQuery()
    {
        return ResourceCompliance::createViewContractorResourceComplianceByHiringOrgPositionQuery();
    }

    public function createViewContractorResourceOverallComplianceQuery()
    {
        return ResourceCompliance::createViewContractorResourceOverallComplianceQuery();
    }

    public function createViewContractorResourcePositionRequirementsQuery()
    {
        return ResourceCompliance::createViewContractorResourcePositionRequirementsQuery();
    }
}
