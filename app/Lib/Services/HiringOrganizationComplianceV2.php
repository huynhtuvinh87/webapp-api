<?php

namespace App\Lib\Services;

use App\Models\Contractor;
use App\Models\HiringOrganization;
use App\Models\Role;
use App\Traits\CacheTrait;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Log;

/**
 * Hiring Organization Compliance V2
 * Andrew Lampert
 * 22 May 2020
 *
 * This class is to compute the hiring organization dashboard metrics.
 * Methods in here are all static so they can be called easily without having to spawn new instances of this class
 * IMPORTANT: Be sure to update the HiringOrganizationComplianceV2Test file when making any changes
 */
class HiringOrganizationComplianceV2
{
    use CacheTrait;

    /* ----------------------------- Query Builders ----------------------------- */

    /**
     * Gets list of requirements assigned to contractor / employees
     *
     * @param HiringOrganization $hiringOrg
     * @param [type] $filter
     * @return void
     */
    public static function getAssignedRequirementInfo(HiringOrganization $hiringOrg = null, $filter = null): Builder
    {

        $hiringOrgIdParam = null;

        if (isset($hiringOrg)) {
            $hiringOrgIdParam = DB::raw($hiringOrg->id);
        }

        $contractorOwnerSubQuery = DB::table("roles")
            ->where("roles.role", DB::raw("'owner'"))
            ->where("roles.entity_key", DB::raw("'contractor'"))
            ->whereNull("roles.deleted_at")
            ->select(
                "entity_id as contractor_id",
                DB::raw("MIN(roles.id) as role_id"),
            )
            ->groupBy(
                "roles.entity_id"
            );

        /** @var Builder List of active contractors to hiring org */
        $contractorsQuery = DB::table("contractor_hiring_organization")
            ->join("contractors", "contractors.id", "contractor_hiring_organization.contractor_id")
            ->where("contractor_hiring_organization.accepted", DB::raw(true))
            ->select([
                'contractor_hiring_organization.contractor_id',
                'contractor_hiring_organization.hiring_organization_id',
            ])
            ->groupBy(
                'contractor_hiring_organization.contractor_id',
                'contractor_hiring_organization.hiring_organization_id'
            );

        if (isset($hiringOrgIdParam)) {
            $contractorsQuery
                ->where("contractor_hiring_organization.hiring_organization_id", $hiringOrgIdParam);
        }

        $contractorRequirements = DB::table("contractor_position")
            ->join("position_requirement", function ($join) {
                $join->on("position_requirement.position_id", "contractor_position.position_id");
                $join->on("position_requirement.is_active", DB::raw(true));
            })
            ->join("positions", function ($join) use ($hiringOrg) {
                $join->on("positions.id", "position_requirement.position_id");
                $join->on("positions.is_active", DB::raw(true));
                if (isset($hiringOrg)) {
                    $join->on("positions.hiring_organization_id", DB::raw($hiringOrg->id));
                }
            })
            ->leftJoinSub($contractorOwnerSubQuery, "contractor_owner_role", function ($join) {
                $join->on("contractor_owner_role.contractor_id", "contractor_position.contractor_id");
            })
            ->where("positions.position_type", DB::raw("'contractor'"))
            ->select(
                "positions.hiring_organization_id",
                "contractor_position.contractor_id",
                "contractor_owner_role.role_id",
                DB::raw("NULL as resource_id"),
                "contractor_position.position_id",
                "position_requirement.requirement_id",
                "positions.position_type"
            );

        $resourceRequirements = DB::table("resources")
            ->join("resource_position", "resource_position.resource_id", "resources.id")
            ->join("resource_role", "resource_role.resource_id", "resources.id")
            ->join("positions", function ($join) use ($hiringOrg) {
                $join->on("positions.id", "resource_position.position_id");
            })
            ->join("position_requirement", function ($join) {
                $join->on("position_requirement.position_id", "resource_position.position_id");
                // $join->whereNull("position_role.deleted_at");
            })
            ->join("roles", function ($join) {
                $join->on("roles.id", "resource_role.role_id");
                $join->on("roles.entity_key", DB::raw("'contractor'"));
            })
            ->select(
                "positions.hiring_organization_id",
                "resources.contractor_id",
                "resource_role.role_id",
                "resources.id as resource_id",
                "resource_position.position_id",
                "position_requirement.requirement_id",
                "positions.position_type"
            );

        $employeeRequirements = DB::table("position_role")
            ->join("position_requirement", function ($join) {
                $join->on("position_requirement.position_id", "position_role.position_id");
                $join->whereNull("position_role.deleted_at");
            })
            ->join("positions", function ($join) use ($hiringOrg) {
                $join->on("positions.id", "position_requirement.position_id");
                $join->on("positions.is_active", DB::raw(true));
            })
            ->join("roles", function ($join) {
                $join->on("roles.id", "position_role.role_id");
                $join->on("roles.entity_key", DB::raw("'contractor'"));
            })
            ->join("users", "users.id", "roles.user_id")
            ->where('roles.role', '<>', DB::raw("'owner'"))
            ->where("positions.position_type", DB::raw("'employee'"))
            ->whereNull("position_role.deleted_at")
            ->whereNull("roles.deleted_at")
            // If user's password is not set, they haven't accepted the invite
            ->whereNotNull("users.password")
            ->select(
                "positions.hiring_organization_id",
                "roles.entity_id as contractor_id",
                "position_role.role_id",
                DB::raw("NULL as resource_id"),
                "position_role.position_id",
                "position_requirement.requirement_id",
                "positions.position_type"
            );

        /** @var Builder List of active requirements */
        $activeContractors = DB::table(DB::raw("({$contractorsQuery->toSql()}) as active_contractors"));

        $assignedContractorRequirements = DB::table(DB::raw("({$contractorsQuery->toSql()}) as active_contractors"))
            ->leftJoinSub($contractorRequirements, 'assigned_contractor_requirements', function ($join) {
                $join->on("assigned_contractor_requirements.contractor_id", "active_contractors.contractor_id");
                $join->on("assigned_contractor_requirements.hiring_organization_id", "active_contractors.hiring_organization_id");
            })
            ->select(
                "assigned_contractor_requirements.hiring_organization_id",
                "assigned_contractor_requirements.contractor_id",
                "assigned_contractor_requirements.role_id",
                "assigned_contractor_requirements.resource_id",
                "assigned_contractor_requirements.position_id",
                "assigned_contractor_requirements.requirement_id",
                "assigned_contractor_requirements.position_type"
            );

        $assignedResourceRequirements = DB::table(DB::raw("({$contractorsQuery->toSql()}) as active_contractors"))
            ->leftJoinSub($resourceRequirements, 'assigned_resource_requirements', function ($join) {
                $join->on("assigned_resource_requirements.contractor_id", "active_contractors.contractor_id");
                $join->on("assigned_resource_requirements.hiring_organization_id", "active_contractors.hiring_organization_id");
            })
            ->select(
                "assigned_resource_requirements.hiring_organization_id",
                "assigned_resource_requirements.contractor_id",
                "assigned_resource_requirements.role_id",
                "assigned_resource_requirements.resource_id",
                "assigned_resource_requirements.position_id",
                "assigned_resource_requirements.requirement_id",
                "assigned_resource_requirements.position_type"
            );

        $assignedEmployeeRequirements = DB::table(DB::raw("({$contractorsQuery->toSql()}) as active_contractors"))
            ->leftJoinSub($employeeRequirements, 'assigned_employee_requirements', function ($join) {
                $join->on("assigned_employee_requirements.contractor_id", "active_contractors.contractor_id");
                $join->on("assigned_employee_requirements.hiring_organization_id", "active_contractors.hiring_organization_id");
            })
            ->select(
                "assigned_employee_requirements.hiring_organization_id",
                "assigned_employee_requirements.contractor_id",
                "assigned_employee_requirements.role_id",
                "assigned_employee_requirements.resource_id",
                "assigned_employee_requirements.position_id",
                "assigned_employee_requirements.requirement_id",
                "assigned_employee_requirements.position_type"
            );

        $assignedRequirementsQuery = $assignedContractorRequirements
            ->union($assignedEmployeeRequirements)
            ->union($assignedResourceRequirements);

        // TODO: Combine resource requirements from DEV-1340

        $departmentSubJoin = DB::table("departments")
            ->leftJoin("department_requirement", function ($join) {
                $join->on("department_requirement.department_id", "departments.id");
                $join->whereNull("department_requirement.deleted_at");
            })
            ->select(
                "department_requirement.department_id",
                "department_requirement.requirement_id",
                "departments.hiring_organization_id"
            )
            ->groupBy(
                "department_requirement.department_id",
                "department_requirement.requirement_id",
                "departments.hiring_organization_id"
            );
        if (isset($hiringOrg)) {
            $departmentSubJoin
                ->where("departments.hiring_organization_id", DB::raw($hiringOrg->id));
        }

        $assignedRequirements = DB::table(DB::raw("({$assignedRequirementsQuery->toSql()}) as assigned_requirements"))
            ->leftJoinSub($departmentSubJoin, "hiring_organization_department_requirement", function ($join) {
                $join->on("hiring_organization_department_requirement.requirement_id", "assigned_requirements.requirement_id");
                $join->on("hiring_organization_department_requirement.hiring_organization_id", "assigned_requirements.hiring_organization_id");
            })
            ->whereNotNull("assigned_requirements.hiring_organization_id")
            ->select(
                "assigned_requirements.hiring_organization_id",
                "assigned_requirements.contractor_id",
                "assigned_requirements.role_id",
                "assigned_requirements.resource_id",
                "assigned_requirements.position_id",
                "assigned_requirements.position_type",
                "assigned_requirements.requirement_id",

                self::selectColBuilder("hiring_organization_department_requirement.department_id", "department_ids"),
            )
            ->groupBy(
                "contractor_id",
                "hiring_organization_id",
                "role_id",
                "resource_id",
                "position_id",
                "position_type",
                "requirement_id",
            );

        // Applying Filter

        if (isset($filter)) {
            collect($filter)
                ->filter(function ($filterProp) use ($assignedRequirements) {
                    // TODO: Filter out bad filter arguments
                    $inArray = in_array($filterProp['column'], $assignedRequirements->columns);
                    if (!$inArray) {
                        Log::debug("{$filterProp['column']} is not a column in " . __FUNCTION__, $assignedRequirements->columns);
                    }
                    return $inArray;
                })
                ->each(function ($filterProp) use ($assignedRequirements) {
                    $assignedRequirements->where($filterProp['column'], $filterProp['operator'], $filterProp['value']);
                });
        }

        return $assignedRequirements;

    }

    private static function getRequirementStatusesSelectCols($query)
    {

        $dueDateCol = "requirement_histories.completion_date + INTERVAL requirements.renewal_period MONTH";
        $warningDateCol = "requirement_histories.completion_date + INTERVAL requirements.renewal_period MONTH - INTERVAL requirements.warning_period DAY";

        $isSubmitted = "(requirement_histories.id IS NOT NULL)";
        $isAutoApproved = "(requirements.count_if_not_approved)";
        $isApproved = "(requirement_history_reviews.status = 'approved')";
        $isDeclined = "(requirement_history_reviews.status = 'declined')";
        $isExcluded = "IFNULL(exclusion_requests.status = 'approved', false)";
        $isInWarning = "($warningDateCol < NOW() AND $dueDateCol > NOW())";
        $isExpired = "($dueDateCol < NOW())";

        $isReviwed = "($isApproved OR $isDeclined)";
        $isCompleted = "($isSubmitted AND ($isAutoApproved OR $isApproved) AND NOT $isExpired)";

        $isActive = "(NOT($isExcluded))";
        $isActiveAndCompleted = "(!($isExcluded) AND $isCompleted)";

        return $query
            ->select(
                // IDs
                "hiring_organizations.id as hiring_organization_id",
                "contractors.id as contractor_id",
                "active_requirements.role_id",
                "positions.id as position_id",
                "requirements.id as requirement_id",
                "active_requirements.resource_id",
                "exclusion_requests.id as exclusion_request_id",
                "requirement_histories.id as requirement_history_id",
                "requirement_history_reviews.id as requirement_history_review_id",

                // // Additional info
                "positions.name as position_name",
                "positions.position_type as position_type",
                "requirements.type as requirement_type",
                "requirements.count_if_not_approved as auto_approved",
                // "requirement_history_reviews.status as requirement_history_review_status",

                "active_requirements.department_ids",

                // Dates
                "requirement_histories.completion_date",
                "requirement_histories.created_at as submission_date",
                DB::raw("($warningDateCol) as warning_date"),
                DB::raw("($dueDateCol) as due_date"),

                // Statuses
                DB::raw("IF($isSubmitted, true, null) as is_submitted"),
                DB::raw("IF($isApproved, true, null) as is_approved"),
                DB::raw("IF($isDeclined, true, null) as is_declined"),
                DB::raw("IF($isExcluded, true, null) as is_excluded"),

                DB::raw("IF($isInWarning, true, null) as is_in_warning"),
                DB::raw("IF($isExpired, true, null) as is_expired"),

                DB::raw("IF($isReviwed, true, null) as is_reviewed"),
                DB::raw("IF($isCompleted, true, null) as is_completed"),

                DB::raw("IF($isActive, true, null) as is_active"),
                DB::raw("IF($isActiveAndCompleted, true, null) as is_active_and_completed"),
            );

    }

    /**
     * This method takes the output of getAssignedRequirementInfo, and returns a list of requirements with status columns.
     * The method only returns ids and statuses so the compliance numbers can be easily calculated.
     * The statuses generated by this method are either "true" or "null". This is so because the COUNT method includes "false" as well.
     * NOTE: Columns that are not generated but stored (count_if_not_approved) will have a true / false value, rather than true / null.
     */
    public static function getRequirementStatuses(HiringOrganization $hiringOrg = null, $filter = null)
    {

        // Error Handling
        if (isset($hiringOrg)) {
            if (!isset($hiringOrg->id)) {
                throw new Exception("Hiring Organization ID was not defined");
            }
            // Parameters
            $hiringOrgIdParam = DB::raw($hiringOrg->id);
        }

        $requirementsQuery = self::getAssignedRequirementInfo($hiringOrg, $filter);

        if (!isset($requirementsQuery)) {
            throw new Exception("getAssignedRequirementInfo returned null");
        }

        // ----- Max Querries ----- //
        $orderedRequirementHistories = DB::table("requirement_histories")
            ->join("requirements", function ($join) {
                $join->on("requirements.id", "requirement_histories.requirement_id");
            })
            ->orderBy("requirement_histories.completion_date");

        if (isset($hiringOrg)) {
            $orderedRequirementHistories->where("requirements.hiring_organization_id", DB::raw($hiringOrg->id));
        }

        $maxCorporateRequirementHistories = DB::table("requirement_histories")
        // Filtering only relevant requirements to improve execution time
            ->join("requirements", function ($join) {
                $join->on("requirements.id", "requirement_histories.requirement_id");
            })
        // NOTE: Need to get contractor owner in case no role is defined
        // Need to create a sub query to get only 1 owner. In case there are multiple, should only return 1 record
        //->leftJoin("roles", function($join){
        //$join->on("roles.contractor_id", "requirement_histories.contractor_id");
        //});
            ->groupBy(
                'requirement_id',
                "contractor_id",
                "resource_id"
            )
            ->select(
                DB::raw("MAX(requirement_histories.id) as requirement_history_id"),
                'requirement_histories.requirement_id',
                "requirement_histories.contractor_id",
                "requirement_histories.resource_id"
            );

        if (isset($hiringOrg)) {
            $maxCorporateRequirementHistories
                ->where("requirements.hiring_organization_id", DB::raw($hiringOrg->id));
        }

        $tmpMaxRequirementHistories = DB::table(DB::raw("({$maxCorporateRequirementHistories->toSql()}) as tbl"));

        $maxRequirementHistories = DB::table("requirement_histories")
        // Filtering only relevant requirements to improve execution time
            ->join("requirements", function ($join) {
                $join->on("requirements.id", "requirement_histories.requirement_id");
            })
        // NOTE: Need to get contractor owner in case no role is defined
        // Need to create a sub query to get only 1 owner. In case there are multiple, should only return 1 record
        //->leftJoin("roles", function($join){
        //$join->on("roles.contractor_id", "requirement_histories.contractor_id");
        //});
            ->groupBy(
                'requirement_id',
                "role_id",
                "contractor_id",
                "resource_id"
            )
            ->select(
                DB::raw("MAX(requirement_histories.id) as requirement_history_id"),
                'requirement_id',
                "role_id",
                "contractor_id",
                "resource_id"
            );

        if (isset($hiringOrg)) {
            $maxRequirementHistories
                ->where("requirements.hiring_organization_id", DB::raw($hiringOrg->id));
        }

        $maxCorporateExclusionRequestIDQuery = DB::table("exclusion_requests")
            ->join("requirements", "requirements.id", "exclusion_requests.requirement_id")
            ->select(
                DB::raw("MAX(exclusion_requests.id) as exclusion_request_id"),
                "exclusion_requests.requirement_id",
                "exclusion_requests.contractor_id",
            )
            ->groupBy(
                "exclusion_requests.requirement_id",
                "exclusion_requests.contractor_id",
            );

        if (isset($hiringOrg)) {
            $maxCorporateExclusionRequestIDQuery
                ->where("requirements.hiring_organization_id", $hiringOrgIdParam);
        }

        $maxExclusionRequestIDQuery = DB::table("exclusion_requests")
            ->join("requirements", "requirements.id", "exclusion_requests.requirement_id")
            ->select(
                DB::raw("MAX(exclusion_requests.id) as exclusion_request_id"),
                "exclusion_requests.requirement_id",
                "exclusion_requests.contractor_id",
                "exclusion_requests.requester_role_id"
                // TODO: Resources
            )
            ->groupBy(
                "exclusion_requests.requirement_id",
                "exclusion_requests.contractor_id",
                "exclusion_requests.requester_role_id"
            );

        if (isset($hiringOrg)) {
            $maxExclusionRequestIDQuery
                ->where("requirements.hiring_organization_id", $hiringOrgIdParam);
        }

        $maxRequirementHistoryReviews = DB::table("requirement_history_reviews")
            ->select(
                DB::raw("MAX(requirement_history_reviews.id) as requirement_history_review_id"),
                "requirement_history_reviews.requirement_history_id"
            )
            ->groupBy(
                "requirement_history_reviews.requirement_history_id"
            );

        // ----- Corporate Requirement Statuses ----- //

        /** @var Builder Requirement Statuses only for corporate requirements */
        $corporateRequirementStatusesQuery = DB::table(DB::raw("({$requirementsQuery->toSql()}) as active_requirements"));

        // Submitter information
        $corporateRequirementStatusesQuery
            ->join("hiring_organizations", "hiring_organizations.id", "active_requirements.hiring_organization_id")
            ->join("contractors", "contractors.id", "active_requirements.contractor_id");

        // Requirement Information
        $corporateRequirementStatusesQuery
            ->join("positions", function ($join) {
                $join->on("positions.id", "active_requirements.position_id");
                $join->on("positions.hiring_organization_id", "hiring_organizations.id");
                $join->on("positions.position_type", DB::raw("'contractor'"));
                $join->on("positions.is_active", DB::raw(true));
            })
            ->join("requirements", function ($join) {
                $join->on("requirements.id", "active_requirements.requirement_id");
                $join->on("requirements.hiring_organization_id", "hiring_organizations.id");
            });

        // Submission information
        $corporateRequirementStatusesQuery
            ->leftJoinSub($maxCorporateRequirementHistories, "max_requirement_histories", function ($join) {
                $join->on("max_requirement_histories.requirement_id", "active_requirements.requirement_id");
                // $join->on(function($subJoin){
                // $subJoin->on("max_requirement_histories.role_id", "active_requirements.role_id");
                // $subJoin->orOn(DB::raw("active_requirements.role_id IS NULL"));
                // });
                $join->on("max_requirement_histories.contractor_id", "active_requirements.contractor_id");
            })
            ->leftJoin("requirement_histories", "requirement_histories.id", "max_requirement_histories.requirement_history_id");

        // $corporateRequirementStatusesQuery
        //     ->leftJoin("roles", function ($join) {
        //         $join->on("roles.entity_key", DB::raw("'contractor'"));
        //         $join->on("roles.entity_id", "requirement_histories.contractor_id");
        //         $join->on(function ($subQuery) {
        //             $subQuery->on("roles.id", "requirement_histories.role_id");
        //             $subQuery->on("roles.role", DB::raw("'owner'"));
        //         });
        //     });

        $corporateRequirementStatusesQuery
            ->leftJoinSub($maxCorporateExclusionRequestIDQuery, "max_exclusion_requests", function ($join) {
                $join->on("max_exclusion_requests.requirement_id", "requirements.id");
                $join->on("max_exclusion_requests.contractor_id", "contractors.id");
                // $join->on("max_exclusion_requests.requester_role_id", "roles.id");
            })
            ->leftJoin("exclusion_requests", "exclusion_requests.id", "max_exclusion_requests.exclusion_request_id");

        $corporateRequirementStatusesQuery
            ->leftJoinSub($maxRequirementHistoryReviews, 'max_requirement_history_reviews', function ($join) {
                $join->on("max_requirement_history_reviews.requirement_history_id", "requirement_histories.id");
            });
        $corporateRequirementStatusesQuery
            ->leftJoin("requirement_history_reviews", "max_requirement_history_reviews.requirement_history_review_id", "requirement_history_reviews.id");

        // Selects
        self::getRequirementStatusesSelectCols($corporateRequirementStatusesQuery);

        // ----- Employee Requirement Statuses ----- //

        /** @var Builder Requirement Statuses only for corporate requirements */
        $employeeRequirementStatusesQuery = DB::table(DB::raw("({$requirementsQuery->toSql()}) as active_requirements"));
        // Submitter information
        $employeeRequirementStatusesQuery
            ->join("hiring_organizations", "hiring_organizations.id", "active_requirements.hiring_organization_id")
            ->join("contractors", "contractors.id", "active_requirements.contractor_id");

        // Requirement Information
        $employeeRequirementStatusesQuery
            ->join("positions", function ($join) {
                $join->on("positions.id", "active_requirements.position_id");
                $join->on("positions.hiring_organization_id", "hiring_organizations.id");
                $join->on("positions.position_type", DB::raw("'employee'"));
            })
            ->join("requirements", function ($join) {
                $join->on("requirements.id", "active_requirements.requirement_id");
                $join->on("requirements.hiring_organization_id", "hiring_organizations.id");
            });

        // Submission information
        $employeeRequirementStatusesQuery
            ->leftJoinSub($maxRequirementHistories, "max_requirement_histories", function ($join) {
                $join->on("max_requirement_histories.requirement_id", "active_requirements.requirement_id");
                $join->on("max_requirement_histories.role_id", "active_requirements.role_id");
                $join->on("max_requirement_histories.contractor_id", "active_requirements.contractor_id");
            })
            ->leftJoin("requirement_histories", "requirement_histories.id", "max_requirement_histories.requirement_history_id");

        $employeeRequirementStatusesQuery
            ->leftJoin("roles", function ($join) {
                $join->on(function ($subQuery) {
                    $subQuery->on("roles.id", "requirement_histories.role_id");
                    $subQuery->on("roles.entity_key", DB::raw("'contractor'"));
                    $subQuery->on("roles.entity_id", "requirement_histories.contractor_id");
                });
            });

        $employeeRequirementStatusesQuery
            ->leftJoinSub($maxExclusionRequestIDQuery, "max_exclusion_requests", function ($join) {
                $join->on("max_exclusion_requests.requirement_id", "requirements.id");
                // NOTE: This was removed as some legacy submissions don't record the contractor ID
                // $join->on("max_exclusion_requests.contractor_id", "contractors.id");
                $join->on("max_exclusion_requests.requester_role_id", "active_requirements.role_id");
            })
            ->leftJoin("exclusion_requests", "exclusion_requests.id", "max_exclusion_requests.exclusion_request_id");

        $employeeRequirementStatusesQuery
            ->leftJoin("requirement_history_reviews", "requirement_history_reviews.requirement_history_id", "requirement_histories.id");

        self::getRequirementStatusesSelectCols($employeeRequirementStatusesQuery);

        // ----- Resource Requirement Statuses ----- //

        /** @var Builder Requirement Statuses only for corporate requirements */
        $resourceRequirementStatusesQuery = DB::table(DB::raw("({$requirementsQuery->toSql()}) as active_requirements"));
        // Submitter information
        $resourceRequirementStatusesQuery
            ->join("hiring_organizations", "hiring_organizations.id", "active_requirements.hiring_organization_id")
            ->join("contractors", "contractors.id", "active_requirements.contractor_id");

        // Requirement Information
        $resourceRequirementStatusesQuery
            ->join("positions", function ($join) {
                $join->on("positions.id", "active_requirements.position_id");
                $join->on("positions.hiring_organization_id", "hiring_organizations.id");
                $join->on("positions.position_type", DB::raw("'resource'"));
            })
            ->join("requirements", function ($join) {
                $join->on("requirements.id", "active_requirements.requirement_id");
                $join->on("requirements.hiring_organization_id", "hiring_organizations.id");
            });

        // Submission information
        $resourceRequirementStatusesQuery
            ->leftJoinSub($maxRequirementHistories, "max_requirement_histories", function ($join) {
                $join->on("max_requirement_histories.requirement_id", "active_requirements.requirement_id");
                $join->on("max_requirement_histories.contractor_id", "active_requirements.contractor_id");
                $join->on("max_requirement_histories.resource_id", "active_requirements.resource_id");
            })
            ->leftJoin("requirement_histories", "requirement_histories.id", "max_requirement_histories.requirement_history_id");

        $resourceRequirementStatusesQuery
            ->leftJoin("roles", function ($join) {
                $join->on(function ($subQuery) {
                    $subQuery->on("roles.id", "requirement_histories.role_id");
                    $subQuery->on("roles.entity_key", DB::raw("'contractor'"));
                    $subQuery->on("roles.entity_id", "requirement_histories.contractor_id");
                });
            });

        $resourceRequirementStatusesQuery
            ->leftJoinSub($maxExclusionRequestIDQuery, "max_exclusion_requests", function ($join) {
                $join->on("max_exclusion_requests.requirement_id", "requirements.id");
                $join->on("max_exclusion_requests.contractor_id", "contractors.id");
                $join->on("max_exclusion_requests.requester_role_id", "roles.id");
            })
            ->leftJoin("exclusion_requests", "exclusion_requests.id", "max_exclusion_requests.exclusion_request_id");

        $resourceRequirementStatusesQuery
            ->leftJoin("requirement_history_reviews", "requirement_history_reviews.requirement_history_id", "requirement_histories.id");

        self::getRequirementStatusesSelectCols($resourceRequirementStatusesQuery);

        // ----- Combining ----- //

        $requirementStatusesQuery = $corporateRequirementStatusesQuery
            ->union($resourceRequirementStatusesQuery)
            ->union($employeeRequirementStatusesQuery);

        return $requirementStatusesQuery;
    }

    // ===== Corporate Compliance Level ===== //

    public static function getOverallComplianceQuery(HiringOrganization $hiringOrg, $filter = null)
    {
        $requirementStatusesQuery = self::getRequirementStatuses($hiringOrg, $filter);
        $corporateComplianceQuery = DB::table(DB::raw("({$requirementStatusesQuery->toSql()}) as requirement_statuses"));
        // ->where(function ($where) {
        //     $where->where("requirement_statuses.position_type", DB::raw("'contractor'"));
        //     $where->orWhere("requirement_statuses.position_type", DB::raw("'resource'"));
        // });

        $requirementCountColQuery = "COUNT(requirement_statuses.is_active)";
        $requirementCompletedCountColQuery = "COUNT(requirement_statuses.is_active_and_completed)";

        $corporateComplianceQuery
            ->select(
                "requirement_statuses.position_type",
                DB::raw("$requirementCountColQuery as requirement_count"),
                DB::raw("$requirementCompletedCountColQuery as requirement_completed_count"),
                DB::raw("FLOOR(($requirementCompletedCountColQuery / $requirementCountColQuery)*100) as compliance"),
            )
            ->groupBy("requirement_statuses.position_type");

        return $corporateComplianceQuery;
    }

    public static function getCorporateOverallCompliance(HiringOrganization $hiringOrg, $filter = [])
    {
        $result = self::getOverallComplianceQuery($hiringOrg)
            ->where('position_type', 'contractor')
            ->first()
            ->compliance;

        return $result;
    }

    // ===== Compliance By Contractor ===== //

    public static function getContractorsWithComplianceQuery(HiringOrganization $hiringOrg, $filter = [])
    {

        // Ratings sub query
        $maxRatingSubQuery = DB::table("ratings")
            ->select(
                DB::raw("MAX(ratings.id) as rating_id"),
                "ratings.contractor_id",
                "ratings.hiring_organization_id"
            )
            ->groupBy(
                "ratings.contractor_id",
                "ratings.hiring_organization_id"
            );

        $maxSubscriptionSubQuery = DB::table("subscriptions")
            ->select(
                DB::raw("MAX(subscriptions.id) as subscription_id"),
                "subscriptions.contractor_id",
            )
            ->groupBy(
                "subscriptions.contractor_id",
            );

        $corporateRequirementCountColQuery = "SUM(
            IF(
                requirement_statuses.is_active IS NOT NULL
                AND
                requirement_statuses.position_type IN ( 'contractor', 'resource' ),
                1,
                0
            )
        )";
        $corporateRequirementCompletedCountColQuery = "SUM(
            IF(
                requirement_statuses.is_active_and_completed IS NOT NULL
                AND
                requirement_statuses.position_type IN ( 'contractor', 'resource' ), 1, 0
            )
        )";

        $employeeRequirementCountColQuery = "SUM(
            IFNULL(
                IF(
                    requirement_statuses.is_active IS NOT NULL
                    AND
                    requirement_statuses.position_type = 'employee', 1, 0
                ), 0
            )
        )";
        $employeeRequirementCompletedCountColQuery = "SUM(
            IFNULL(
                IF(
                    requirement_statuses.is_active_and_completed IS NOT NULL
                    AND
                    requirement_statuses.position_type = 'employee', 1, 0
                ), 0
            )
        )";

        $subscriptionIsActiveColQuery = "IF(
            subscriptions.id IS NOT NULL
            AND
            (
                subscriptions.ends_at IS NULL
                OR
                subscriptions.ends_at > NOW()
            ), true, false
        )";

        $requirementStatusesQuery = self::getRequirementStatuses($hiringOrg);
        $complianceQuery = DB::table(DB::raw("({$requirementStatusesQuery->toSql()}) as requirement_statuses"));

        $complianceQuery
            ->join("contractor_hiring_organization", function ($join) use ($hiringOrg) {
                $join->on("contractor_hiring_organization.contractor_id", "requirement_statuses.contractor_id");
                $join->on("contractor_hiring_organization.hiring_organization_id", DB::raw($hiringOrg->id));
            })
            ->join("contractors", "contractors.id", "contractor_hiring_organization.contractor_id")
            // Subscriptions
            ->leftJoinSub($maxSubscriptionSubQuery, "max_subscription", function ($join) {
                $join->on("max_subscription.contractor_id", "requirement_statuses.contractor_id");
            })
            ->leftJoin("subscriptions", "subscriptions.id", "max_subscription.subscription_id")
            // Getting facility information
            ->leftJoin("contractor_facility", "contractor_facility.contractor_id", "contractors.id")
            ->leftJoin("facilities", function ($join) {
                $join->on("facilities.id", "contractor_facility.facility_id");
                $join->on("facilities.hiring_organization_id", "contractor_hiring_organization.hiring_organization_id");
            })
            ->select(
                // Contractor information
                "contractors.id",
                "contractors.id as contractor_id",
                "contractors.name",

                "requirement_statuses.hiring_organization_id",

                // Subscription Information
                DB::raw("$subscriptionIsActiveColQuery as is_active"),
                DB::raw("IF(subscriptions.ends_at < NOW(), true, false) as is_expired_subscription"),

                // Connection to Hiring Org Information
                DB::raw("MAX(contractor_hiring_organization.accepted) as is_accepted"),

                DB::raw("MAX(requirement_statuses.department_ids) as department_ids"),
                self::selectColBuilder("facilities.id", "facility_ids"),
                self::selectColBuilder("requirement_statuses.position_id", "position_ids"),

                // Compliance Information
                DB::raw("$corporateRequirementCountColQuery as corporate_requirement_count"),
                DB::raw("$corporateRequirementCompletedCountColQuery as corporate_completed_requirement_count"),
                DB::raw("$employeeRequirementCountColQuery as employee_requirement_count"),
                DB::raw("$employeeRequirementCompletedCountColQuery as employee_completed_requirement_count"),
                DB::raw("FLOOR(($corporateRequirementCompletedCountColQuery / $corporateRequirementCountColQuery)*100) as contractor_compliance"),
                DB::raw("FLOOR(($employeeRequirementCompletedCountColQuery / $employeeRequirementCountColQuery)*100) as employee_compliance"),
            )
            ->groupBy("contractors.id", "requirement_statuses.hiring_organization_id");

        $contractorsWithComplianceAndRatings = DB::table(DB::raw("({$complianceQuery->toSql()}) as contractorsWithCompliance"))
        // Ratings
            ->leftJoinSub($maxRatingSubQuery, "max_rating", function ($join) {
                $join->on("max_rating.contractor_id", "contractorsWithCompliance.contractor_id");
                $join->on("max_rating.hiring_organization_id", "contractorsWithCompliance.hiring_organization_id");
            })
            ->leftJoin("ratings", function ($join) {
                $join->on("ratings.contractor_id", "contractorsWithCompliance.contractor_id");
                $join->on("ratings.id", "max_rating.rating_id");
            })
            ->select(
                "contractorsWithCompliance.*",

                // Rating information
                "ratings.rating",
                "ratings.comments as rating_comment",
            );

        return $contractorsWithComplianceAndRatings;

    }

    public static function getContractorsWithCompliance(Role $requestRole, HiringOrganization $hiringOrg, $filter = null)
    {
        $contractorsComplianceQuery = self::getContractorsWithComplianceQuery($hiringOrg, $filter);
        $contractorsQuery = DB::table(DB::raw("({$contractorsComplianceQuery->toSql()}) as contractors_with_compliance"));

        if ($requestRole->role != 'owner') {
            // Default filter based on role
            $facilityRoles = DB::table("facility_role")
                ->where('facility_role.role_id', DB::raw($requestRole->id))
                ->select('facility_role.facility_id')
                ->get()
                ->map(function ($set) {
                    return $set->facility_id;
                })
                ->toArray();

            if (!isset($filter)) {
                $filter = [];
            }

            // Appending facility Role IDs to filter
            if (sizeof($facilityRoles) > 0) {
                collect($facilityRoles)
                    ->each(function ($facilityRole) use ($contractorsQuery) {
                        $contractorsQuery->orHavingRaw("facility_ids LIKE '%($facilityRole)%'");
                    });
            }
        }

        if (isset($filter)) {
            collect(array_keys($filter))
                ->each(function ($key) use ($contractorsQuery, $filter) {

                    // Making sure keys are ordered
                    $keys = collect($filter[$key])->sort()->toArray();
                    $filterOptionsStr = join(')%(', $keys);

                    switch ($key) {
                        case 'facility':
                            // Appending facility Role IDs
                            $contractorsQuery
                                ->having('facility_ids', "LIKE", DB::raw("'%(" . $filterOptionsStr . ")%'"));
                            break;
                        case 'position':
                            $contractorsQuery
                                ->having('position_ids', "LIKE", DB::raw("'%(" . $filterOptionsStr . ")%'"));
                            break;
                        case 'department':
                            $contractorsQuery
                                ->having('department_ids', "LIKE", DB::raw("'%(" . $filterOptionsStr . ")%'"));
                            break;
                    }
                });
        }

        $complianceRes = $contractorsQuery->get()
            ->toArray();

        $contractorsWithCompliance = Contractor::hydrate($complianceRes);

        return $contractorsWithCompliance;
    }

    // ===== Compliance By Positions ===== //

    public static function getContractorComplianceByPositionQuery(Contractor $contractor, HiringOrganization $hiringOrg, $filter = [])
    {

        if(!isset($contractor)){
            throw new Exception("Contractor was not defined");
        }

        if(!isset($hiringOrg)){
            throw new Exception("Hiring Org was not defined");
        }

        // Appending filters to query
        // Matching contractor
        $filter[] = (
            [
                'column' => 'assigned_requirements.contractor_id',
                'operator' => '=',
                'value' => DB::raw($contractor->id),
            ]);
        // Contractor position type
        $filter[] = (
            [
                'column' => 'assigned_requirements.position_type',
                'operator' => '=',
                'value' => DB::raw("'contractor'"),
            ]
        );

        /** Records of requirement statuses */
        $requirementStatusesQueryRaw = self::getRequirementStatuses($hiringOrg, $filter);
        $requirementStatusesQuery = DB::table(DB::raw("({$requirementStatusesQueryRaw->toSql()}) as requirement_statuses"));

        $positionComplianceQuery = $requirementStatusesQuery
            ->groupBy('requirement_statuses.position_id')
            ->select(
                // NOTE: Renamed to id for compatibility with the frontend
                "requirement_statuses.position_id as id",
                // Position Name
                DB::raw("GROUP_CONCAT(DISTINCT requirement_statuses.position_name) as name"),
                // Requirement Count
                DB::raw("COUNT(requirement_statuses.is_active) as requirement_count"),
                // Requirement Completed Count
                DB::raw("COUNT(requirement_statuses.is_active_and_completed) as requirement_completed_count"),
                // Compliance Level
                DB::raw("ROUND((COUNT(requirement_statuses.is_active_and_completed) / COUNT(requirement_statuses.is_active))*100) as compliance"),
            );

        return $positionComplianceQuery;
    }

    /* ------------------------------ Pending Lists ----------------------------- */

    /**
     * Gets a list of the pending requirements
     */
    public static function getPendingRequirementsQuery(HiringOrganization $hiringOrg, Role $role = null, $filters = null)
    {

        $origQuery = HiringOrganizationComplianceV2::getRequirementStatuses($hiringOrg);
        $pendingRequirementsQuery = DB::table(DB::raw("({$origQuery->toSql()}) as pending_requirement_statuses"))
            ->where('pending_requirement_statuses.auto_approved', DB::raw("false"))
            ->whereNull("pending_requirement_statuses.is_expired")
            ->whereNotNull("pending_requirement_statuses.is_active")
            ->whereNotNull('pending_requirement_statuses.is_submitted')
            ->whereNull("pending_requirement_statuses.is_reviewed");

        // Department Filtering
        // If role is set, and it is not the owner, apply department filter
        if (isset($role) && $role->role != 'owner') {
            $pendingRequirementsQuery->where(function ($query) use ($role) {
                $role->departments->each(function ($department) use ($query) {
                    $departmentId = $department->id;
                    $query->orWhere('department_ids', "LIKE", DB::raw("'%($departmentId)%'"));
                });
            });
        }

        $requirementHistoryFiles = DB::table("file_requirement_history")
            ->join("files", "files.id", "file_requirement_history.file_id")
            ->select(
                "requirement_history_id",
                DB::raw("JSON_ARRAYAGG(JSON_OBJECT('id', file_id, 'fullPath', path)) as file_ids")
            )
            ->groupBy("requirement_history_id");

        // NOTE: Brings only 1 requirement content per requirement id
        $maxRequirementContentIdQuery = DB::table("requirement_contents")
            ->select(DB::raw("MAX(requirement_contents.id) as requirement_content_id"))
            ->groupBy("requirement_contents.requirement_id");
        $requirementContentQuery = DB::table("requirement_contents")
            ->joinSub($maxRequirementContentIdQuery, "max_requirement_content_id", function ($join) {
                $join->on("max_requirement_content_id.requirement_content_id", "requirement_contents.id");
            });

        $pendingRequirementsQuery
            ->leftJoin("contractors", function ($join) {
                $join->on("contractors.id", "pending_requirement_statuses.contractor_id");
            });

        $pendingRequirementsQuery
            ->joinSub($requirementContentQuery, "related_requirement_content", function ($join) {
                $join->on("related_requirement_content.requirement_id", "pending_requirement_statuses.requirement_id");
            });

        $pendingRequirementsQuery
            ->leftJoinSub($requirementHistoryFiles, "grouped_files", function ($join) {
                $join->on("grouped_files.requirement_history_id", "pending_requirement_statuses.requirement_history_id");
            });

        $pendingRequirementsQuery
            ->leftJoin("roles", function ($join) {
                $join->on("roles.id", "pending_requirement_statuses.role_id");
            });

        $pendingRequirementsQuery
            ->leftJoin("users", function ($join) {
                $join->on("users.id", "roles.user_id");
            });

        $pendingRequirementsQuery
            ->leftJoin("contractor_facility", "contractor_facility.contractor_id", "pending_requirement_statuses.contractor_id")
            ->leftJoin("facilities", function ($join) use ($hiringOrg) {
                $join->on("facilities.id", "contractor_facility.facility_id");
                $join->on("facilities.hiring_organization_id", DB::raw($hiringOrg->id));
            })

        ;

        $pendingRequirementsQuery
            ->groupBy("pending_requirement_statuses.contractor_id",
                "pending_requirement_statuses.role_id",
                "pending_requirement_statuses.position_type",
                "pending_requirement_statuses.requirement_id",
                "pending_requirement_statuses.requirement_type",
                "pending_requirement_statuses.requirement_history_id",
                "related_requirement_content.name",
                "pending_requirement_statuses.submission_date",
                "pending_requirement_statuses.due_date"
            );

        $pendingRequirementsQuery
            ->select(
                "pending_requirement_statuses.contractor_id as contractor_id",
                "contractors.name as contractor_name",

                "users.id as user_id",
                "pending_requirement_statuses.role_id as role_id",
                "users.first_name",
                "users.last_name",

                "pending_requirement_statuses.position_type",

                "pending_requirement_statuses.requirement_id",
                "related_requirement_content.name as name",
                "pending_requirement_statuses.requirement_type as type",
                "pending_requirement_statuses.requirement_history_id as requirement_history_id",
                // "requirement_history_reviews.id as requirement_history_review_id",
                "pending_requirement_statuses.submission_date as created_at",
                // "pending_requirement_statuses.requirement_type",
                "pending_requirement_statuses.due_date",

                self::selectColBuilder("facilities.id", "facility_ids"),
                self::selectColBuilder("pending_requirement_statuses.position_id", "position_ids"),
                DB::raw("(grouped_files.file_ids)")
            );

        return $pendingRequirementsQuery;
    }

    public static function getPendingRequirements(HiringOrganization $hiringOrg, $filters = null)
    {
        $query = self::getPendingRequirementsQuery($hiringOrg, $filters);
        return $query->get();
    }

    /* ---------------------------- Helper Functions ---------------------------- */

    /**
     * Method to test the filter param
     */
    private static function testFilter($filter)
    {
        // If the filter isn't even set, just return
        if (!isset($filter)) {
            return true;
        }
        $collectedFilter = collect($filter);

        // Checking filter keys
        $validKeys = ['position', 'department', 'facility'];
        $filterKeys = $collectedFilter->keys();
        $badKeys = $filterKeys->diff($validKeys);

        if ($badKeys->count() > 0) {
            throw new Exception("Bad Keys");
        }

        $collectedFilter->each(function ($filterVal) {
            if (!is_array($filterVal)) {
                throw new Exception("Filter did not have arrays");
            }
        });

        return true;
    }

    private static function selectColBuilder($column, $columnName)
    {
        return DB::raw("CONCAT('(', GROUP_CONCAT(distinct $column ORDER BY $column SEPARATOR '),('), ')') as '$columnName'");
    }

}
