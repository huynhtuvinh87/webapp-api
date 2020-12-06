<?php

use App\Models\Facility;
use App\Models\HiringOrganization;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

class CreateNotInScopeFacility extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        try {
            DB::beginTransaction();

            $hiringOrgs = HiringOrganization::get();

            $query = $hiringOrgs
                ->each(function ($hiringOrg) {
                    $newFacilityName = "Not In Scope";
                    $hiringOrgFacilities = DB::table("facilities")
                        ->where("facilities.hiring_organization_id", $hiringOrg->id)
                        ->where("facilities.name", DB::raw("'$newFacilityName'"));

                    Log::debug("Hiring Organization - $hiringOrg->name", [
                        "facility count" => $hiringOrgFacilities->count(),
                    ]);

                    // Adding the "Not In Scope" facility
                    $newFacility = Facility::firstOrCreate([
                        "hiring_organization_id" => $hiringOrg->id,
                        "name" => $newFacilityName,
                    ], [
                        "description" => "Facility for legacy contractors that did not have a facility.",
                    ]);

                    // Getting the hiring org contractors with no facilities
                    $contractorsWithNoFacilitiesQuery = DB::table("contractor_hiring_organization")
                        ->join("contractors", "contractors.id", "contractor_hiring_organization.contractor_id")
                        ->join("hiring_organizations", "hiring_organizations.id", "contractor_hiring_organization.hiring_organization_id")
                        ->where("contractor_hiring_organization.hiring_organization_id", DB::raw($hiringOrg->id))
                        ->leftJoin("contractor_facility", function ($join) {
                            $join->on("contractor_facility.contractor_id", "contractor_hiring_organization.contractor_id");
                        })
                        ->leftJoin("facilities", function ($join) {
                            $join->on("facilities.hiring_organization_id", "contractor_hiring_organization.hiring_organization_id");
                            $join->on("facilities.id", "contractor_facility.facility_id");
                        })
                        ->whereNull("contractor_facility.id")
                        ->select(
                            "contractor_hiring_organization.contractor_id",
                            "contractor_hiring_organization.hiring_organization_id",
                            DB::raw("COUNT(contractor_facility.facility_id) as facility_count")
                        )
                        ->groupBy(
                            "contractor_hiring_organization.contractor_id",
                            "contractor_hiring_organization.hiring_organization_id",
                        );

                    $contractorsWithNoFacilitiesQuery
                        ->get()
                        ->map(function ($data) use ($newFacility) {
                            Log::debug("New Facility: " . $newFacility->id);

                            if (isset($newFacility) && $data->facility_count == 0) {
                                $insertData = [
                                    "facility_id" => DB::raw($newFacility->id),
                                    "contractor_id" => DB::raw($data->contractor_id),
                                    "created_at" => Carbon::now(),
                                    "updated_at" => Carbon::now(),
                                ];

                                Log::debug("Inserting into contractor_facility table", $insertData);

                                DB::table("contractor_facility")
                                    ->insert($insertData);
                            }
                        });

                });

            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::debug(__METHOD__, ['exception' => $ex->getMessage()]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
