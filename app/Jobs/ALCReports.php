<?php

namespace App\Jobs;

use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Log;

class ALCReports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $disk = "alc_sftp";
    /* ------------------------------- Job Methods ------------------------------ */

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->export();
    }

    /* --------------------------------- Helpers -------------------------------- */

    public function collectionToCSV(Collection $collection, $fileName, $disk = "local")
    {

        if (sizeof($collection) == 0) {
            Storage::disk($disk)->put($fileName, "");
            Log::debug("No data was written to $fileName - Collection was empty");
            return false;
        }

        // Getting column names from collection
        $columns = array_keys(get_object_vars($collection->first()));

        $csvExporter = new \Laracsv\Export();
        $csvExporter->build($collection, $columns);
        $csvWriter = $csvExporter->getWriter();
        $csvString = $csvWriter->getContent(); // To get the CSV as string

        Storage::disk($disk)->put($fileName, $csvString);

        // Verifying it was uploaded
        $exists = Storage::disk($disk)->exists($fileName);
        if (!$exists) {
            throw new Exception("File $fileName was not uploaded correctly to $disk");
        }
    }

    /**
     * queryToCSV
     * Takes in a query and file name, and uploads it to the specified disk
     */
    public function queryToCSV(Builder $query, $fileName, $disk = 'local')
    {
        $collection = $query->get();
        return $this->collectionToCSV($collection, $fileName, $disk);
    }

    /* ----------------------------- Query Builders ----------------------------- */

    public function export()
    {
        $dateTimeStr = Carbon::now()->format("Ymd_Hi");

        $spComplianceName = "sp_compliance-" . $dateTimeStr;
        $spEmployeeComplianceName = "sp_employee_compliance-" . $dateTimeStr;
        $driverInformation = "driver_information-" . $dateTimeStr;
        $internalDriverInformation = "internal_driver_information-" . $dateTimeStr;
        $internalServiceProviderInformation = "internal_service_provider_information-" . $dateTimeStr;

        $serviceProviderInformation = "service_provider_information-" . $dateTimeStr;
        $vehicleInformation = "vehicle_information-" . $dateTimeStr;
        $internalStudentMonitorInformation = "internal_student_monitor_information-" . $dateTimeStr;
        $studentMonitorInformation = "student_monitor_information-" . $dateTimeStr;

        //Compliance
        $this->createServiceProviderComplianceCsv($spComplianceName, $this->disk);
        $this->createServiceProviderEmployeeDistrictComplianceCsv($spEmployeeComplianceName, $this->disk);

        // Driver Information
        $this->createDriverInformationCsv($driverInformation, $this->disk);
        $this->createInternalDriverInformationCsv($internalDriverInformation, $this->disk);

        //Service Provider Information
        $this->createInternalServiceProviderInformationCsv($internalServiceProviderInformation, $this->disk);
        $this->createServiceProviderInformationCsv($serviceProviderInformation, $this->disk);

        //Vehicle Information
        $this->createVehicleInformationCsv($vehicleInformation, $this->disk);

        //Student Monitor Information
        $this->createInternalStudentMonitorInformationCsv($internalStudentMonitorInformation, $this->disk);
        $this->createStudentMonitorInformationCsv($studentMonitorInformation, $this->disk);

    }

    public function createServiceProviderComplianceCsv($fileName, $disk)
    {
        try {
            // TODO: Check disk
            $fileName = $fileName . '.csv';

            $service_providers = collect(DB::select('SELECT
                    distinct c.name as \'service_provider\',
                    c.external_id as \'service_provider_external_id\',
                    c.second_external_id as \'service_provider_second_external_id\',
                    f.name as \'state_name\',
                    \'TBD\' as \'state_code\',
                    IF(vccbho.compliance = 100 AND vcoc.requirement_count = vcoc.requirements_completed_count, 1, 0) as \'compliant\'
                FROM contractors c
                JOIN contractor_hiring_organization cho ON cho.contractor_id = c.id
                LEFT JOIN contractor_position cp ON cp.contractor_id = c.id
                LEFT JOIN positions p ON p.id = cp.position_id
                LEFT JOIN facility_position fp on fp.position_id = p.id
                LEFT JOIN facilities f on f.id = fp.facility_id
                    AND f.id = fp.facility_id
                LEFT JOIN view_contractor_resource_compliance_by_hiring_org_position vccbho ON vccbho.hiring_organization_id = cho.hiring_organization_id
                    AND vccbho.position_id = cp.position_id
                LEFT JOIN view_contractor_overall_compliance vcoc ON vcoc.contractor_id = c.id
                LEFT JOIN states ON states.name = f.name
                -- LEFT JOIN account_name_agency_code anac ON anac.state_name = states.sortname
                WHERE cho.hiring_organization_id = 144
                AND c.id not in (12411) -- Test contractor
                ORDER BY c.name'
            ));

            // NOTE: STarted working on all of this, but realized this was supposed to be elsewhere.
            // Leaving it in for if/when we decide to move over to query builders

            // $query = DB::table("contractor_hiring_organization")
            //     ->join("contractors", function ($join) {
            //         $join->on("contractors.id", "contractor_hiring_organization.contractor_id");
            //     });

            // // Employee information
            // $query->join("roles", function ($join) {
            //     $join->on("roles.entity_key", DB::raw("'contractor'"));
            //     $join->on("roles.entity_id", "contractor_hiring_organization.contractor_id");
            // });

            // // Position Information
            // // $query
            // //     ->join("contractor_position", "contractor_position.contractor_id", "contractors.id")
            // //     ->join('positions', function ($join) {
            // //         $join->on('positions.id', 'contractor_position.position_id');
            // //         $join->on("positions.hiring_organization_id", "contractor_hiring_organization.hiring_organization_id");
            // //     });
            // $query
            //     ->join("position_role", "position_role.role_id", "roles.id")
            //     ->join('positions', function ($join) {
            //         $join->on('positions.id', 'position_role.position_id');
            //         $join->on("positions.hiring_organization_id", "contractor_hiring_organization.hiring_organization_id");
            //     });

            // $query
            //     ->leftJoin("contractor_facility", "contractor_facility.contractor_id", "contractor_hiring_organization.contractor_id")
            //     ->leftJoin("facilities", function ($join) {
            //         $join->on("facilities.id", "contractor_facility.facility_id");
            //         $join->on("facilities.hiring_organization_id", "contractor_hiring_organization.hiring_organization_id");
            //     })
            //     ->leftJoin("alc_district_state_codes", function ($join) {
            //         $join->on(DB::raw("CONCAT(alc_district_state_codes.district, '%')"), "LIKE", "positions.name");
            //         // $join->on("alc_district_state_codes.state", "facilities.name");
            //     });

            // // Compliance
            // $query
            //     ->join("view_contractor_resource_compliance_by_hiring_org_position as vccbho", function ($join) {
            //         $join->on("vccbho.hiring_organization_id", "contractor_hiring_organization.hiring_organization_id");
            //     });
            // $query
            //     ->join("view_contractor_overall_compliance as vcoc", function ($join) {
            //         $join->on("vcoc.contractor_id", "contractors.id");
            //     });

            // $query->select([
            //     // Contractor Info
            //     "contractors.name as service_provider",
            //     "contractors.external_id as service_provider_external_id",
            //     "contractors.second_external_id as service_provider_second_external_id",

            //     // Position
            //     "positions.name as position_name",
            //     "facilities.name as facility_name",
            //     "alc_district_state_codes.*",

            //     // Compliance
            //     DB::raw("IF(vccbho.compliance = 100 AND vcoc.requirement_count = vcoc.requirements_completed_count, 1, 0) as 'compliant'"),
            // ]);

            // $query->where('contractor_hiring_organization.hiring_organization_id', DB::raw(144));

            // // TESTING
            // $query->where("positions.name", "LIKE", DB::raw("'Ace Charter School%'"));

            // $results = $query->get();

            $this->collectionToCSV($service_providers, $fileName, $disk);

            return true;
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            // return false;
            throw $ex;
        }
    }

    public function createServiceProviderEmployeeDistrictComplianceCsv($fileName, $disk)
    {
        // sp_employee_compliance
        try {

            // TODO: Check disk
            $fileName = $fileName . '.csv';

            $origQuery = 'SELECT DISTINCT
            c.name as \'service_provider\',
            c.external_id as \'service_provider_external_id\',
            c.second_external_id as \'service_provider_second_external_id\',
            u.first_name,
            u.last_name,
            r.external_id as \'employee_external_id\',
            r.second_external_id as \'employee_second_external_id\',
            res.name as \'resource_name\',
            resourceP.id as \'resource_id\',
            resourceP.name as \'district\',
            \'rhapsody state name\' as \'state_name\',
            IF(vcrcbho.compliance = 100, 1, 0) as \'resource_is_compliant\',
            (vccbho.requirement_count = vccbho.requirements_completed_count) as \'service_provider_is_compliant\',
            (veoc.requirement_count = veoc.requirements_completed_count ) as \'employee_is_compliant\',
            (vcrcbho.compliance = 100 AND vccbho.requirement_count = vccbho.requirements_completed_count AND veoc.requirement_count = veoc.requirements_completed_count) as \'compliant\',
            COALESCE(rr.id IS NOT NULL, 0) as \'assigned_vehicle\',
            IF (resourceP.name like \'%Monitor%\', 1, 0) as \'is_monitor\',
            IF (resourceP.name like \'%Driver%\', 1, 0) as \'is_driver\'
            FROM contractors c
            LEFT JOIN contractor_hiring_organization cho ON cho.contractor_id = c.id
            LEFT JOIN roles r on r.entity_id = c.id
            LEFT JOIN users u on u.id = r.user_id
            LEFT JOIN position_role prole ON prole.role_id = r.id
            LEFT JOIN positions p ON p.id = prole.position_id
            LEFT JOIN facility_position fp on fp.position_id = p.id
            LEFT JOIN positions resourceP on resourceP.id = p.id
            LEFT JOIN facilities f on f.hiring_organization_id = cho.hiring_organization_id
            LEFT JOIN position_requirement preq on preq.position_id = p.id
            LEFT JOIN requirements reqs ON reqs.id = preq.requirement_id
            LEFT JOIN requirement_contents rc on rc.requirement_id = reqs.id
            LEFT JOIN resource_role rr on rr.role_id = r.id
                AND f.id = fp.facility_id
            LEFT JOIN view_contractor_resource_compliance_by_hiring_org_position vcrcbho ON vcrcbho.hiring_organization_id = cho.hiring_organization_id
                AND vcrcbho.position_id = prole.position_id
            LEFT JOIN resources res on res.id = rr.resource_id
            LEFT JOIN view_contractor_compliance_by_hiring_org vccbho
                ON vccbho.contractor_id = c.id
            LEFT JOIN view_employee_overall_compliance veoc
                ON veoc.role_id = prole.role_id
            WHERE cho.hiring_organization_id = 144';

            // $result = collect(DB::select($origQuery));

            // Contractor Information
            $employeeDistrictQuery = DB::table("contractor_hiring_organization")
                ->join('contractors', function ($join) {
                    $join->on('contractors.id', 'contractor_hiring_organization.contractor_id');
                });

            // User Information
            $employeeDistrictQuery
                ->join("roles", function ($join) {
                    $join->on("roles.entity_id", "contractors.id");
                    $join->on("roles.entity_key", DB::raw("'contractor'"));
                })
                ->join("users", function ($join) {
                    $join->on("users.id", "roles.user_id");
                });

            // Role position information
            $employeeDistrictQuery
                ->leftJoin("position_role", "position_role.role_id", "roles.id");
            // ->leftJoin("positions as positions_for_role", function ($join) {
            //     $join->on("positions_for_role.id", "position_role.position_id");
            //     $join->on("positions_for_role.hiring_organization_id", "contractor_hiring_organization.hiring_organization_id");
            // });

            // Resource Information
            $employeeDistrictQuery
                ->leftJoin("resource_role", "resource_role.role_id", "roles.id")
                ->leftJoin("resources", function ($join) {
                    $join->on("resources.id", "resource_role.resource_id");
                    $join->on("resources.contractor_id", "roles.entity_id");
                });
            $employeeDistrictQuery
                ->leftJoin("resource_position", "resource_position.resource_id", "resources.id");
            // ->leftJoin("positions as positions_for_resource", function ($join) {
            //     $join->on("positions_for_resource.id", "resource_position.position_id");
            //     $join->on("positions_for_resource.hiring_organization_id", "contractor_hiring_organization.hiring_organization_id");
            // });

            // General Position Information
            $employeeDistrictQuery
                ->leftJoin("positions as employee_resource_position", function ($join) {
                    $join->on(function ($subJoin) {
                        $subJoin->on("employee_resource_position.id", "position_role.position_id");
                        $subJoin->orOn("employee_resource_position.id", "resource_position.position_id");
                    });
                    $join->on("employee_resource_position.hiring_organization_id", "contractor_hiring_organization.hiring_organization_id");
                });

            // ALC District
            $employeeDistrictQuery
                ->leftJoin("alc_district_state_codes", function ($join) {
                    $join->on("employee_resource_position.name", "LIKE", DB::raw("CONCAT('%', alc_district_state_codes.district ,'%')"));
                });

            $employeeDistrictQuery
                ->select(
                    // Contractor Information
                    "contractors.id as cc_contractor_id",
                    "contractors.name as service_provider_name",
                    "contractors.external_id as service_provider_external_id",
                    "contractors.second_external_id as service_provider_second_external_id",

                    // Employee
                    "roles.id as cc_role_id",
                    "users.first_name as employee_first_name",
                    "users.last_name as employee_last_name",
                    "roles.external_id as employee_external_id",
                    "roles.second_external_id as employee_second_external_id",

                    // Resource Information
                    DB::raw("COALESCE(resource_role.id IS NOT NULL, 0) as assigned_vehicle"),
                    "resources.name as resource_name",
                    "resources.external_id as resource_external_id",

                    "alc_district_state_codes.district as AccountName",
                    "alc_district_state_codes.code as AgencyCode", // AgencyCode is their internal label for the district code
                );

            // Compliance
            $query = $employeeDistrictQuery
                ->leftJoin("view_contractor_resource_compliance_by_hiring_org_position as vcrcbho", function ($join) {
                    $join->on("vcrcbho.contractor_id", "contractor_hiring_organization.contractor_id");
                    $join->on("vcrcbho.hiring_organization_id", "contractor_hiring_organization.hiring_organization_id");
                    $join->on("vcrcbho.position_id", "resource_position.position_id");
                    $join->on("vcrcbho.resource_id", "resource_position.resource_id");
                });

            $query->leftJoin("view_employee_overall_compliance as veoc", function ($join) {
                $join->on("veoc.role_id", "roles.id");
                $join->on("veoc.entity_key", DB::raw("'contractor'"));
                $join->on("veoc.entity_id", "roles.entity_id");
            });

            $query->leftJoin("view_contractor_compliance_by_hiring_org as vccbho", function ($join) {
                $join->on("vccbho.contractor_id", "contractor_hiring_organization.contractor_id");
                $join->on("vccbho.hiring_organization_id", "contractor_hiring_organization.hiring_organization_id");
            });

            $query
            // 144 = ALC
            ->where('contractor_hiring_organization.hiring_organization_id', DB::raw(144));

            $query->select(
                // Contractor Information
                "contractors.id as cc_contractor_id",
                "contractors.name as service_provider_name",
                "contractors.external_id as service_provider_external_id",
                "contractors.second_external_id as service_provider_second_external_id",

                // Employee
                "roles.id as cc_role_id",
                "users.first_name as employee_first_name",
                "users.last_name as employee_last_name",
                "roles.external_id as employee_external_id",
                "roles.second_external_id as employee_second_external_id",

                // Resource Information
                DB::raw("COALESCE(resource_role.id IS NOT NULL, 0) as assigned_vehicle"),
                "resources.name as resource_name",
                "resources.external_id as resource_external_id",

                "employee_resource_position.name as position_name",
                // DB::raw("IF(position_role.position_id IS NOT NULL, 'is_employee_position', ''"),
                "employee_resource_position.position_type",
                "alc_district_state_codes.district as AccountName",
                "alc_district_state_codes.code as AgencyCode", // AgencyCode is their internal label for the district code

                // Compliance
                DB::raw("IF(vcrcbho.compliance = 100, 1, 0) as resource_is_compliant"),
                DB::raw("(vccbho.requirement_count = vccbho.requirements_completed_count) as service_provider_is_compliant"),
                DB::raw("(veoc.requirement_count = veoc.requirements_completed_count ) as employee_is_compliant"),
                DB::raw("(
                    vcrcbho.compliance = 100
                    AND vccbho.requirement_count = vccbho.requirements_completed_count
                    AND veoc.requirement_count = veoc.requirements_completed_count
                ) as compliant"),

            );

            Storage::put(__FUNCTION__ . ".sql", $query->toSql());
            $res = $query->get();

            $this->collectionToCSV($res, $fileName, $disk);
            return true;

        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            // return false;
            throw $ex;
        }
    }

    public function createDriverInformationCsv($fileName, $disk)
    {
        try {

            // TODO: Check disk
            $fileName = $fileName . '.csv';

            $result = collect(DB::select('WITH driverForms as (
                select DISTINCT
                    u.id as \'cc_user_id\',
                    c.id as \'cc_contractor_id\',
                    roles.id as \'role_id\',
                    u.email,
                    c.name as \'service_provider_name\',
                    c.external_id as \'service_provider_external_id\',
                    c.second_external_id as \'service_provider_second_external_id\',
                    roles.external_id as \'employee_external_id\',
                    roles.second_external_id as \'employee_second_external_id\',
                    u.first_name,
                    u.last_name,
                    df.title,
                    dfc.label as \'question_text\',
                    dfsd.value,
                    dfs.id as \'submission_id\'
                from dynamic_forms df
            JOIN dynamic_form_submissions dfs on dfs.dynamic_form_id = df.id
            JOIN dynamic_form_columns dfc on dfc.dynamic_form_id = df.id
            JOIN dynamic_form_submission_data dfsd ON dfsd.dynamic_form_submission_id = dfs.id
                AND dfsd.dynamic_form_column_id = dfc.id
            JOIN roles ON roles.id = dfs.create_role_id
            JOIN users u on u.id = roles.user_id
            JOIN contractors c on c.id = roles.entity_id
                AND roles.entity_key = \'contractor\'
            WHERE 1=1
            AND u.id is not null
            AND df.id IN (88)
            AND dfc.type NOT IN (\'label\')
                AND dfsd.value IS NOT NULL
                AND c.id NOT IN (23, 12411) -- ABC Contractings, Test Contractor
                AND u.id not in (26492, 28660, 28661) -- Testy, Driver Without, Driver With
            )
            SELECT
                df.cc_user_id,
                df.cc_contractor_id,
                -- CASE WHEN p.name like \'%Monitor%\' THEN p.id ELSE 0 END as \'cc_monitor_position_id\',
                -- CASE WHEN p.name like \'%Driver%\' THEN p.id ELSE 0 END as \'cc_driver_position_id\',
                df.service_provider_external_id,
                df.service_provider_second_external_id,
                df.employee_external_id,
                df.employee_second_external_id,
                -- df.alc_employee_id,
                df.submission_id,
                df.first_name,
                df.last_name,
                df.email,
                df.service_provider_name,
                p.name as \'position_name\',
                JSON_ARRAYAGG(JSON_OBJECT(\'answer\', df.value, \'question\', df.question_text)) as \'form_data\'
            FROM driverForms df
                JOIN position_role pr on pr.role_id = df.role_id
                JOIN positions p on p.id = pr.position_id
            GROUP BY
                df.cc_user_id,
                df.cc_contractor_id,
                df.service_provider_external_id,
                df.service_provider_second_external_id,
                df.employee_external_id,
                df.employee_second_external_id,
                df.first_name,
                df.last_name,
                p.name,
                p.id,
                df.submission_id'
            ));

            $this->collectionToCSV($result, $fileName, $disk);

            return true;
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            // return false;
            throw $ex;
        }
    }

    public function createVehicleInformationCsv($fileName, $disk)
    {
        try {

            // TODO: Check disk
            $fileName = $fileName . '.csv';

            $result = collect(DB::select('select DISTINCT
            u.id as \'cc_user_id\',
            c.id as \'cc_contractor_id\',
            c.external_id as \'service_provider_external_id\',
            c.second_external_id as \'service_provider_second_external_id\',
            roles.id as \'cc_role_id\',
            roles.external_id as \'employee_external_id\',
            roles.second_external_id as \'employee_second_external_id\',
            u.first_name,
            u.last_name,
            c.name as \'service_provider_name\',
            df.title,
            dfs.id as submission_id,
            dfc.label as \'question_text\',
            dfsd.value
            from dynamic_forms df
        JOIN dynamic_form_submissions dfs on dfs.dynamic_form_id = df.id
        JOIN dynamic_form_columns dfc on dfc.dynamic_form_id = df.id
        JOIN dynamic_form_submission_data dfsd ON dfsd.dynamic_form_submission_id = dfs.id
            AND dfsd.dynamic_form_column_id = dfc.id
        JOIN roles ON roles.id = dfs.create_role_id
        JOIN users u on u.id = roles.user_id
        JOIN contractors c on c.id = roles.entity_id
        WHERE 1=1
            AND u.id is not null
            AND df.id IN (89)
            AND dfc.type NOT IN (\'label\')
            AND dfsd.value IS NOT NULL
            AND c.id NOT IN (23, 12411, 12667) -- ABC Contractings, Test Contractor
            AND u.id not in (26492, 28660, 28661) -- Testy, Driver Without, Driver With'
            ));

            $this->collectionToCSV($result, $fileName, $disk);

            return true;
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            // return false;
            throw $ex;
        }
    }
    public function createInternalServiceProviderInformationCsv($fileName, $disk)
    {
        try {

            // TODO: Check disk
            $fileName = $fileName . '.csv';

            $result = collect(DB::select('select DISTINCT
            u.id as \'cc_user_id\',
            c.id as \'cc_contractor_id\',
            c.external_id as \'service_provider_external_id\',
            c.second_external_id as \'service_provider_second_external_id\',
            roles.external_id as \'employee_external_id\',
            roles.second_external_id as \'employee_second_external_id\',
            u.first_name,
            u.last_name,
            c.name as \'service_provider_name\',
            df.title,
            dfs.id as submission_id,
            dfc.label as \'question_text\',
            dfsd.value
            from dynamic_forms df
        JOIN dynamic_form_submissions dfs on dfs.dynamic_form_id = df.id
        JOIN dynamic_form_columns dfc on dfc.dynamic_form_id = df.id
        JOIN dynamic_form_submission_data dfsd ON dfsd.dynamic_form_submission_id = dfs.id
            AND dfsd.dynamic_form_column_id = dfc.id
        JOIN roles ON roles.id = dfs.create_role_id
        JOIN users u on u.id = roles.user_id
        JOIN contractors c on c.id = roles.entity_id
        WHERE 1=1
            AND u.id is not null
            AND df.id IN (79)
            AND dfc.type NOT IN (\'label\')
            AND dfsd.value IS NOT NULL
            AND c.id NOT IN (23, 12411, 12667) -- ABC Contractings, Test Contractor
            AND u.id not in (26492, 28660, 28661) -- Testy, Driver Without, Driver With'
            ));

            $this->collectionToCSV($result, $fileName, $disk);
            return true;
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            // return false;
            throw $ex;
        }
    }
    public function createServiceProviderInformationCsv($fileName, $disk)
    {
        try {

            $fileName = $fileName . '.csv';

            // Form Information
            $query = DB::table("dynamic_form_submission_data")
                ->join("dynamic_form_columns", "dynamic_form_columns.id", "dynamic_form_submission_data.dynamic_form_column_id")
                ->join("dynamic_form_submissions", "dynamic_form_submissions.id", "dynamic_form_submission_data.dynamic_form_submission_id")
                ->join("dynamic_forms", "dynamic_forms.id", "dynamic_form_submissions.dynamic_form_id");

            // Requirement Information
            $query
                ->join("requirement_histories", "requirement_histories.id", "dynamic_form_submissions.requirement_history_id")
                ->join("requirements", function ($join) {
                    $join->on("requirements.id", "requirement_histories.requirement_id");
                    // Optimizations
                    $join->on("requirements.hiring_organization_id", "dynamic_forms.hiring_organization_id");
                });

            // Target Information
            $query
                ->join("contractors", "contractors.id", "requirement_histories.contractor_id");

            // Submitter information
            $query
                ->join("roles as submitter_role", "submitter_role.id", "dynamic_form_submissions.create_role_id")
                ->join("users as submitter_user", "submitter_user.id", "submitter_role.user_id");

            // Select
            $query
                ->select(
                    "contractors.name as service_provider_name",
                    "contractors.external_id as service_provider_external_id",
                    "contractors.second_external_id as service_provider_second_external_id",
                    "dynamic_form_submissions.id as submission_id",
                    "dynamic_form_columns.label as question_text",
                    "dynamic_form_submission_data.value"
                );

            $query
                ->whereIn('dynamic_forms.id', [
                    // Form 78
                    DB::raw(78),
                ])
                // No labels
                ->where('dynamic_form_columns.type', "<>", DB::raw("'label'"))
                //  ABC Contractings, Test Contractor
                ->whereNotIn('contractors.id', [
                    DB::raw(23),
                    DB::raw(12411),
                    DB::raw(12667),
                ])
                // Testy, Driver Without, Driver With
                ->whereNotIn("submitter_user.id", [
                    DB::raw(26492),
                    DB::raw(28660),
                    DB::raw(28661),
                ]);

            $res = $query->get();

            $this->collectionToCSV($res, $fileName, $disk);
            return true;
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            // return false;
            throw $ex;
        }
    }
    public function createInternalDriverInformationCsv($fileName, $disk)
    {
        $alcID = 144;
        // form_id 80
        try {

            // TODO: Check disk
            $fileName = $fileName . '.csv';

            // Form Information
            $query = DB::table("dynamic_forms")
                ->join("dynamic_form_columns", "dynamic_form_columns.dynamic_form_id", "dynamic_forms.id");

            // Submission data
            $query
                ->join("dynamic_form_submissions", "dynamic_form_submissions.dynamic_form_id", "dynamic_forms.id")
                ->join("dynamic_form_submission_data", function ($join) {
                    $join->on("dynamic_form_submission_data.dynamic_form_submission_id", "dynamic_form_submissions.id");
                    $join->on("dynamic_form_submission_data.dynamic_form_column_id", "dynamic_form_columns.id");
                });

            // Who submission was for
            $query
                ->join("requirement_histories", "requirement_histories.id", "dynamic_form_submissions.requirement_history_id");

            $query
                ->join("contractor_hiring_organization", function ($join) use ($alcID) {
                    $join->on("contractor_hiring_organization.contractor_id", "requirement_histories.contractor_id");
                    $join->on("contractor_hiring_organization.hiring_organization_id", "dynamic_forms.hiring_organization_id");
                    $join->on("contractor_hiring_organization.hiring_organization_id", DB::raw($alcID));
                })
                ->join("contractors as target_contractors", "target_contractors.id", "contractor_hiring_organization.contractor_id");

            $query
                ->join("roles as target_role", "target_role.id", "requirement_histories.role_id");

            // Form 80 - Internal Driver information
            $query->where("dynamic_forms.id", DB::raw(80));
            // No labels
            $query->where('dynamic_form_columns.type', "<>", DB::raw("'label'"));
            // Hiring Org
            $query->where("contractor_hiring_organization.hiring_organization_id", DB::raw($alcID));

            // Selects
            $query->select(
                // CC IDs
                'target_role.id as cc_history_role_id',
                'target_contractors.id as cc_history_contractor_id',
                "dynamic_form_submissions.id as cc_submission_id",

                // ALC IDs
                "target_contractors.external_id as service_provider_external_id",
                "target_contractors.second_external_id as service_provider_second_external_id",
                "target_role.external_id as employee_external_id",
                "target_role.second_external_id as employee_second_external_id",

                // Submission data
                "dynamic_form_columns.label as question",
                "dynamic_form_submission_data.value as value",

            );
            $query->orderBy("cc_submission_id");

            // Storage::put(__FUNCTION__ . ".sql", $query->toSql());

            $result = $query->get();

            $this->collectionToCSV($result, $fileName, $disk);
            return true;
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            // return false;
            throw $ex;
        }
    }

    public function createInternalStudentMonitorInformationCsv($fileName, $disk)
    {
        // form_id 90
        try {

            // TODO: Check disk
            $fileName = $fileName . '.csv';

            $result = collect(DB::select('select DISTINCT
            u.id as \'cc_user_id\',
            c.id as \'cc_contractor_id\',
            c.external_id as \'service_provider_external_id\',
            c.second_external_id as \'service_provider_second_external_id\',
            roles.external_id as \'employee_external_id\',
            roles.second_external_id as \'employee_second_external_id\',
            u.first_name,
            u.last_name,
            c.name as \'service_provider_name\',
            df.title,
            dfs.id as submission_id,
            dfc.label as \'question_text\',
            dfsd.value
            from dynamic_forms df
        JOIN dynamic_form_submissions dfs on dfs.dynamic_form_id = df.id
        JOIN dynamic_form_columns dfc on dfc.dynamic_form_id = df.id
        JOIN dynamic_form_submission_data dfsd ON dfsd.dynamic_form_submission_id = dfs.id
            AND dfsd.dynamic_form_column_id = dfc.id
        JOIN roles ON roles.id = dfs.create_role_id
        JOIN users u on u.id = roles.user_id
        JOIN contractors c on c.id = roles.entity_id
        WHERE 1=1
            AND u.id is not null
            AND df.id IN (90)
            AND dfc.type NOT IN (\'label\')
            AND dfsd.value IS NOT NULL
            AND c.id NOT IN (23, 12411, 12667) -- ABC Contractings, Test Contractor
            AND u.id not in (26492, 28660, 28661) -- Testy, Driver Without, Driver With'
            ));

            $this->collectionToCSV($result, $fileName, $disk);
            return true;
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            // return false;
            throw $ex;
        }
    }
    public function createStudentMonitorInformationCsv($fileName, $disk)
    {
        // form_id 86
        try {

            // TODO: Check disk
            $fileName = $fileName . '.csv';

            $result = collect(DB::select('select DISTINCT
            u.id as \'cc_user_id\',
            c.id as \'cc_contractor_id\',
            c.external_id as \'service_provider_external_id\',
            c.second_external_id as \'service_provider_second_external_id\',
            roles.external_id as \'employee_external_id\',
            roles.second_external_id as \'employee_second_external_id\',
            u.first_name,
            u.last_name,
            c.name as \'service_provider_name\',
            df.title,
            dfs.id as submission_id,
            dfc.label as \'question_text\',
            dfsd.value
            from dynamic_forms df
        JOIN dynamic_form_submissions dfs on dfs.dynamic_form_id = df.id
        JOIN dynamic_form_columns dfc on dfc.dynamic_form_id = df.id
        JOIN dynamic_form_submission_data dfsd ON dfsd.dynamic_form_submission_id = dfs.id
            AND dfsd.dynamic_form_column_id = dfc.id
        JOIN roles ON roles.id = dfs.create_role_id
        JOIN users u on u.id = roles.user_id
        JOIN contractors c on c.id = roles.entity_id
        WHERE 1=1
            AND u.id is not null
            AND df.id IN (86)
            AND dfc.type NOT IN (\'label\')
            AND dfsd.value IS NOT NULL
            AND c.id NOT IN (23, 12411, 12667) -- ABC Contractings, Test Contractor
            AND u.id not in (26492, 28660, 28661) -- Testy, Driver Without, Driver With'
            ));

            $this->collectionToCSV($result, $fileName, $disk);
            return true;
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            // return false;
            throw $ex;
        }
    }
}
