<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Lib\Services\HiringOrganizationComplianceV2;
use App\Lib\Traits\ReportControllerTrait;
use App\Models\HiringOrganization;
use App\ViewModels\ViewContractorComplianceByHiringOrg;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;

class HiringOrganizationReportController extends Controller
{
    use ReportControllerTrait;

    public function contractorPositions(Request $request)
    {
        $data = DB::table('positions')
            ->join('contractor_position', 'positions.id', '=', 'contractor_position.position_id')
            ->join('contractors', 'contractors.id', '=', 'contractor_position.contractor_id')
            ->where('positions.hiring_organization_id', '=', $request->user()->role->entity_id)
            ->where('positions.is_active', '=', 1)
            ->select(DB::raw('contractors.name as contractor, GROUP_CONCAT(positions.name) as positions'))
            ->groupBy('contractors.name')
            ->get();

        $headers = [
            [
                'text' => 'Contractor',
                'value' => 'contractor',
            ],
            [
                'text' => 'Positions',
                'value' => 'positions',
            ],
        ];

        return $this->report($request, $data, $headers);
    }

    public function employeePositions(Request $request)
    {
        $data = DB::table('positions')
            ->join('contractor_position', 'positions.id', '=', 'contractor_position.position_id')
            ->join('contractors', 'contractors.id', '=', 'contractor_position.contractor_id')
            ->join('position_role', 'position_role.position_id', '=', 'positions.id')
            ->join('roles', 'position_role.role_id', '=', 'roles.id')
            ->join('users', 'roles.user_id', '=', 'users.id')
            ->where('positions.hiring_organization_id', '=', $request->user()->role->entity_id)
            ->where('positions.is_active', '=', 1)
            ->select(DB::raw('contractors.name as contractor, GROUP_CONCAT(positions.name) as positions, Concat(users.first_name, \' \', users.last_name) as employee'))
            ->groupBy('contractors.name', 'users.first_name', 'users.last_name', 'contractors.id')
            ->orderBy('contractors.id')
            ->get();

        $headers = [
            [
                'text' => 'Contractor',
                'value' => 'contractor',
            ],
            [
                'text' => 'Positions',
                'value' => 'positions',
            ],
            [
                'text' => 'Employee',
                'value' => 'employee',
            ],
        ];

        return $this->report($request, $data, $headers);
    }

    public function corporateCompliance(Request $request)
    {

        $data = ViewContractorComplianceByHiringOrg::where('view_contractor_compliance_by_hiring_org.hiring_organization_id', $request->user()->role->entity_id)
            ->join('contractors', 'contractors.id', '=', 'view_contractor_compliance_by_hiring_org.contractor_id')
            ->join('contractor_facility', 'contractor_facility.contractor_id', '=', 'contractors.id')
            ->join('facilities', function($join){
                $join->on('facilities.id', '=', 'contractor_facility.facility_id');
                $join->on("facilities.hiring_organization_id", "view_contractor_compliance_by_hiring_org.hiring_organization_id");
            })
            ->select([
                'contractors.name as contractor_name',
                'view_contractor_compliance_by_hiring_org.*',
                DB::raw("GROUP_CONCAT(facilities.name SEPARATOR ',') as facilities")
            ])
            ->groupBy(
                'view_contractor_compliance_by_hiring_org.contractor_id',
                'view_contractor_compliance_by_hiring_org.hiring_organization_id',
                'view_contractor_compliance_by_hiring_org.requirement_count',
                'view_contractor_compliance_by_hiring_org.requirements_completed_count',
                'view_contractor_compliance_by_hiring_org.contractor_id'
            )
            ->get();

        $newData = [];
        $count = count($data);

        for ($i = 0; $i < $count; $i++) {
            $newData[$i]['name'] = $data[$i]->contractor_name;
            $newData[$i]['requirement_count'] = $data[$i]->requirement_count ?? "NA";
            $newData[$i]['contractor_compliance'] = $data[$i]->compliance;
            $newData[$i]['facilities'] = $data[$i]->facilities;

        }

        $headers = [
            [
                'text' => 'Contractor',
                'value' => 'name',
            ],
            [
                'text' => 'Total Requirements',
                'value' => 'requirement_count',
            ],
            [
                'text' => 'Compliance Percentage',
                'value' => 'contractor_compliance',
            ],
            [
                'text' => 'Facilities',
                'value' => 'facilities',
            ]
        ];

        return $this->report($request, $newData, $headers);

    }

    public function employeeCompliance(Request $request)
    {

        $results = DB::table('view_employee_compliance_by_hiring_org AS vecbho')
            ->join('roles AS r', 'r.id', '=', 'vecbho.role_id')
            ->join('users AS u', 'u.id', '=', 'r.user_id')
            ->join('contractors AS c', function ($join) {
                $join->on('c.id', 'r.entity_id');
                $join->where('r.entity_key', DB::raw("'contractor'"));
            })
            ->join('contractor_facility', 'contractor_facility.contractor_id', '=', 'c.id')
            ->join("facilities", function($join){
                $join->on('facilities.id', '=', 'contractor_facility.facility_id');
                $join->on("facilities.hiring_organization_id", "vecbho.hiring_organization_id");
            })
            ->whereNotNull('u.first_name')
            ->whereNull('r.deleted_at')
            ->where('vecbho.hiring_organization_id', DB::raw($request->user()->role->entity_id))
            ->select(
                DB::raw("CONCAT(u.first_name, ' ', IF(u.last_name != '', u.last_name, '')) AS employee_name"),
                'c.name AS contractor_name',
                'vecbho.requirement_count AS requirement_count',
                DB::raw("round(((vecbho.requirements_completed_count / vecbho.requirement_count )*100)) AS compliance"),
                DB::raw("GROUP_CONCAT(facilities.name SEPARATOR ',') as facilities")
            )
            ->groupBy(
                'employee_name',
                'contractor_name',
                'requirement_count',
                'compliance'
            )
            ->get();

        $newData = [];
        $count = count($results);
        for ($i = 0; $i < $count; $i++) {
            $newData[$i]['employee_name'] = $results[$i]->employee_name;
            $newData[$i]['contractor_name'] = $results[$i]->contractor_name;
            $newData[$i]['requirement_count'] = $results[$i]->requirement_count ?? "NA";
            $newData[$i]['employee_compliance'] = $results[$i]->compliance;
            $newData[$i]['facilities'] = $results[$i]->facilities;
        }

        $headers = [
            [
                'text' => 'Employee Name',
                'value' => 'employee_name',
            ],
            [
                'text' => 'Contractor Name',
                'value' => 'contractor_name',
            ],
            [
                'text' => 'Total Requirements',
                'value' => 'requirement_count',
            ],
            [
                'text' => 'Compliance Percentage',
                'value' => 'employee_compliance',
            ],
            [
                'text' => 'Facilities',
                'value' => 'facilities',
            ]
        ];

        return $this->report($request, $newData, $headers);

    }
    public function requirementsAboutToExpire(Request $request)
    {

        $month_from_today = Carbon::yesterday()->addDays(30)->format('Y-m-d');
        $today = Carbon::today()->format('Y-m-d');

        $data = $request->user()->role->company->contractors()
            ->join('view_contractor_requirements AS view_corporate_requirements',
                function ($query) use ($request) {
                    $query->on(
                        'view_corporate_requirements.contractor_id',
                        '=',
                        'contractors.id')
                        ->where('view_corporate_requirements.hiring_organization_id', $request->user()->role->entity_id);
                })
            ->whereBetween('view_corporate_requirements.due_date', [$today, $month_from_today])
            ->select(DB::raw(
                'contractors.name as contractor,
                view_corporate_requirements.requirement_name as requirement,
                view_corporate_requirements.due_date as expiring_date')
            )
            ->orderBy('view_corporate_requirements.due_date', 'asc')
            ->get()
            ->makeHidden('pivot');

        $headers = [
            [
                'text' => 'Contractor',
                'value' => 'contractor',
            ],
            [
                'text' => 'Requirement',
                'value' => 'requirement',
            ],
            [
                'text' => 'Expiring Date',
                'value' => 'expiring_date',
            ],
        ];

        return $this->report($request, $data, $headers);

    }

    public function requirementsPastDue(Request $request)
    {

        $month_ago = Carbon::yesterday()->subDays(30)->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        $data = $request->user()->role->company->contractors()
            ->join('view_contractor_requirements AS view_corporate_requirements',
                function ($query) use ($request) {
                    $query->on(
                        'view_corporate_requirements.contractor_id', '=', 'contractors.id')
                        ->where('view_corporate_requirements.hiring_organization_id', $request->user()->role->entity_id
                        );
                })
            ->whereBetween('view_corporate_requirements.due_date', [$month_ago, $yesterday])
            ->select(DB::raw(
                'contractors.name as contractor,
                view_corporate_requirements.requirement_name as requirement,
                view_corporate_requirements.due_date as expiration_date')
            )
            ->orderBy('view_corporate_requirements.due_date', 'desc')
            ->get()
            ->makeHidden('pivot');

        $headers = [
            [
                'text' => 'Contractor',
                'value' => 'contractor',
            ],
            [
                'text' => 'Requirement',
                'value' => 'requirement',
            ],
            [
                'text' => 'Expiration Date',
                'value' => 'expiration_date',
            ],
        ];

        return $this->report($request, $data, $headers);

    }

    public function complianceByPosition(Request $request)
    {
        $hiringOrgId = $request->user()->role->company->id;

        $data = $request->user()->role->company
            ->join(
                'view_employee_compliance_by_hiring_org_position',
                function ($query) use ($request) {
                    $query->on(
                        'hiring_organizations.id',
                        '=',
                        'view_employee_compliance_by_hiring_org_position.hiring_organization_id'
                    );
                }
            )

        // Joining the contractor_position to get the contractor information
            ->join(
                'contractor_position',
                function ($query) use ($request) {
                    $query->on(
                        'view_employee_compliance_by_hiring_org_position.position_id',
                        '=',
                        'contractor_position.position_id'
                    );
                }
            )
            ->join(
                'contractors',
                function ($query) use ($request) {
                    $query->on(
                        'contractors.id',
                        '=',
                        'contractor_position.contractor_id'
                    );
                }
            )

        // Joining roles and users to get employee information
            ->join(
                'roles',
                function ($query) use ($request) {
                    $query->on(
                        'roles.id',
                        '=',
                        'view_employee_compliance_by_hiring_org_position.role_id'
                    );
                }
            )
            ->join(
                'users',
                function ($query) use ($request) {
                    $query->on(
                        'users.id',
                        '=',
                        'roles.user_id'
                    );
                }
            )
            ->select(DB::raw(
                // NOTE: Needs to be concatenated to make sure its there???
                'CONCAT("", contractors.name) AS contractor_name,'
                . 'CONCAT(users.first_name, " ", users.last_name) AS user_name,'
                . 'view_employee_compliance_by_hiring_org_position.position_name AS position_name,'
                . '(requirements_completed_count / requirement_count) * 100 as compliance'
            ))
            ->where('hiring_organizations.id', $hiringOrgId)
            ->get()
            ->makeHidden('pivot');

        $headers = [
            [
                'text' => 'Contractor',
                'value' => 'contractor_name',
            ],
            [
                'text' => 'Employee',
                'value' => 'user_name',
            ],
            [
                'text' => 'Employee Position',
                'value' => 'position_name',
            ],
            [
                'text' => 'Compliance %',
                'value' => 'compliance',
            ],
        ];

        return $this->report($request, $data, $headers);

    }

    public function pendingInternalRequirements(HiringOrganization $hiringOrg)
    {
        if (!isset($hiringOrg)) {
            throw new Exception("Hiring Organization from request could not be found");
        }
        if (!isset($hiringOrg->id)) {
            Log::debug(__FUNCTION__, [
                'hiring org name' => $hiringOrg->name,
                'hiring org ID' => $hiringOrg->id,
            ]);
            throw new Exception("Hiring Organization ID was not defined");
        }

        $requirementStatuses = HiringOrganizationComplianceV2::getRequirementStatuses($hiringOrg);

        $requirementContentSubJoin = DB::table("requirement_contents")
            ->where('lang', DB::raw("'en'"))
            ->select(
                'requirement_contents.requirement_id',
                DB::raw("MAX(requirement_contents.name) as name")
            )
            ->groupBy(
                'requirement_contents.requirement_id'
            );

        $requirementsQuery = DB::table(DB::raw("({$requirementStatuses->toSql()}) as requirement_statuses"))
            ->join("requirements", "requirements.id", "requirement_statuses.requirement_id")
            ->leftJoin('contractors', 'contractors.id', 'requirement_statuses.contractor_id')
            ->leftJoin("positions", "positions.id", "requirement_statuses.position_id")

            ->leftJoin("roles", "roles.id", "requirement_statuses.role_id")
            ->leftJoin("users", "users.id", "roles.user_id")

            ->leftJoinSub($requirementContentSubJoin, "requirement_content_sub", function ($join) {
                $join->on("requirement_content_sub.requirement_id", "requirements.id");
            })

            ->where(function ($query) {
                $query->where("requirement_statuses.requirement_type", DB::raw("'internal_document'"));
                $query->orWhere("requirement_statuses.requirement_type", DB::raw("'internal'"));
            })

        // Getting pending internal requirements
            ->where(function ($query) {
                $query->whereNotNull("requirement_statuses.is_expired");
                $query->orWhereNotNull("requirement_statuses.is_in_warning");
                $query->orWhereNull("requirement_statuses.is_submitted");
            })
            ->whereNull("requirement_statuses.is_completed")
            ->whereNotNull("requirement_statuses.is_active")

            ->select(
                // Contractor Name
                DB::raw("contractors.name as contractor_name"),
                // Employee Name
                DB::raw("IF(
                    positions.position_type = 'employee',
                    MAX(CONCAT(users.first_name, ' ', users.last_name)),
                    ''
                ) as user_name"),
                // Requirement Name
                "requirement_content_sub.name as requirement_name",
                // Position Type (Making it generic so it can be used elsewhere)
                DB::raw("(
                    CASE
                        WHEN requirements.type = 'internal_document' THEN 'Internal'
                        WHEN requirements.type = 'internal' THEN 'Internal'
                        WHEN requirements.type = 'upload' THEN 'Upload'
                        -- Splitting text by '_', and making first letter uppercase
                        ELSE CONCAT(UPPER(SUBSTRING(requirements.type,1,1)),LOWER(SUBSTRING(REPLACE(requirements.type, '_', ' '),2)))
                    END
                ) as requirement_type"),
                // positions.position_type with first character uppercase
                DB::raw("CONCAT(UPPER(SUBSTRING(positions.position_type,1,1)),LOWER(SUBSTRING(REPLACE(positions.position_type, '_', ' '),2)))")
            )
            ->groupBy(
                "contractors.name",
                "requirement_content_sub.name",
                "requirements.type",
                "positions.position_type"
            );

        return $requirementsQuery;
    }

    public function pendingInternalRequirementsReport(Request $request)
    {

        try {

            $hiringOrg = $request->user()->role->company;
            if (!isset($hiringOrg)) {
                throw new Exception("Hiring Organization from request could not be found");
            }
            if (!isset($hiringOrg->id)) {
                Log::debug(__FUNCTION__, [
                    'hiring org name' => $hiringOrg->name,
                    'hiring org ID' => $hiringOrg->id,
                ]);
                throw new Exception("Hiring Organization ID was not defined");
            }

            $dataQuery = $this->pendingInternalRequirements($hiringOrg);
            $data = $dataQuery
            // ->where("positions.position_type", "contractor")
            ->get();

            $headers = [
                [
                    'text' => 'Contractor Name',
                    'value' => 'contractor_name',
                ],
                [
                    'text' => 'Employee Name',
                    'value' => 'user_name',
                ],
                [
                    'text' => 'Requirement',
                    'value' => 'requirement_name',
                ],
                [
                    'text' => 'Requirement Type',
                    'value' => 'requirement_type',
                ],
                [
                    'text' => 'Position Type',
                    'value' => 'position_type',
                ],
            ];

            return $this->report($request, $data, $headers);
        } catch (Exception $e) {
            return response(["message" => $e->getMessage()], 500);
        }

    }

    public function pendingEmployeeInvitationReport(Request $request)
    {

        try {

            $hiringOrg = $request->user()->role->company;
            if (!isset($hiringOrg)) {
                throw new Exception("Hiring Organization from request could not be found");
            }
            if (!isset($hiringOrg->id)) {
                Log::debug(__FUNCTION__, [
                    'hiring org name' => $hiringOrg->name,
                    'hiring org ID' => $hiringOrg->id,
                ]);
                throw new Exception("Hiring Organization ID was not defined");
            }

            $dataQuery = DB::table('users AS u')
                ->join("roles AS r", function ($join) {
                    $join->on("r.user_id", "u.id");
                    $join->where('r.entity_key', DB::raw("'contractor'"));
                })
                ->join("facility_role AS fr", "fr.role_id", "r.id")
                ->join("facilities AS f", "f.id", "fr.facility_id")
                ->join("contractors AS c", "c.id", "r.entity_id")
                ->where("f.hiring_organization_id", $hiringOrg->id)
                ->where(function ($query){
                    $query->whereNull("u.password")
                        ->orWhere("u.password", "=", DB::raw("''"));
                })
                ->select(
                    DB::raw("CONCAT(u.first_name, ' ', u.last_name) as employee_name"),
                    'u.email',
                    DB::raw("DATE_FORMAT(u.created_at, '%m-%d-%Y') AS invited_at"),
                    'c.name as contractor_name',
                    'f.name as facility_name'
                );

            $data = $dataQuery
                ->get();

            $headers = [
                [
                    'text' => 'Employee Name',
                    'value' => 'employee_name',
                ],
                [
                    'text' => 'Employee Email',
                    'value' => 'email',
                ],
                [
                    'text' => 'Invited At',
                    'value' => 'invited_at',
                ],
                [
                    'text' => 'Contractor Name',
                    'value' => 'contractor_name',
                ],
                [
                    'text' => 'Facility',
                    'value' => 'facility_name',
                ],
            ];

            return $this->report($request, $data, $headers);
        } catch (Exception $e) {
            return response(["message" => $e->getMessage()], 500);
        }

    }

}
