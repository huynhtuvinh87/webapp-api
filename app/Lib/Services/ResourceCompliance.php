<?php
namespace App\Lib\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class ResourceCompliance
{
    public static function requirementResourceInfoQuery()
    {
        $renewalDateColQuery = "requirement_histories.completion_date + INTERVAL requirements.renewal_period MONTH";
        $warningDateColQuery = "$renewalDateColQuery - INTERVAL requirements.warning_period DAY";

        $isActiveColQuery = "(
            exclusion_requests.id IS NULL
            OR exclusion_requests.status = 'declined'
        )";

        /**
         * Logic to calculate if the requirement is still pending
         */
        $isPendingColQuery = "(
                -- Not Submitted
                requirement_histories.id IS NULL
                OR (
                    -- Submitted
                    requirement_histories.id IS NOT NULL
                    AND (
                        -- Submitted and declined
                        requirement_history_reviews.status = 'declined'
                        OR
                        -- Submitted and past in warning
                        $warningDateColQuery < NOW()
                    )
                )
            )
            AND ( $isActiveColQuery )";

        /**
         * Logic to calculate if a requirement is completed or not
         * Exclusion approved, or (
         *      Submitted AND not expired AND not declined
         * )
         */
        $isCompletedColQuery = "(
            -- Submitted
            requirement_histories.id IS NOT NULL
            -- Not declined
            AND (
                requirement_history_reviews.status <> 'declined'
                OR requirement_history_reviews.id IS NULL
            )
            -- Not expired
            AND $renewalDateColQuery > NOW()
        ) AND (
            $isActiveColQuery
        )";

        /**
         * Logic to determine if a requirement is "Hiring Org" compliant
         * If its not auto approved, it needs to be reviewed to be completed
         */
        $isHiringOrgCompletedColQuery = "(
            -- Submitted
            requirement_histories.id IS NOT NULL
            -- Not declined
            AND (
                requirement_history_reviews.status = 'approved'
                OR requirements.count_if_not_approved = true
                OR requirements.type = 'internal_document'
            )
            -- Not expired
            AND $renewalDateColQuery > NOW()
        ) AND (
            $isActiveColQuery
        )";

        /** @var Builder Query to get the latest requirement history ID for resource */
        $maxRequirementHistorySubQuery = DB::table("requirement_histories")
            ->select([
                DB::raw("MAX(requirement_histories.id) as requirement_history_id"),
                "requirement_histories.requirement_id",
                "requirement_histories.contractor_id",
                // "requirement_histories.role_id",
                "requirement_histories.resource_id",
            ])
            ->groupBy(
                "requirement_histories.requirement_id",
                "requirement_histories.contractor_id",
                // "requirement_histories.role_id",
                "requirement_histories.resource_id"
            )
            ->where('requirement_histories.valid', DB::raw(true));

        $maxExclusionRequestQuery = DB::table("exclusion_requests")
            ->select([
                DB::raw("MAX(exclusion_requests.id) as exclusion_request_id"),
                "exclusion_requests.requirement_id",
                "exclusion_requests.contractor_id",
            ])
            ->groupBy(
                "exclusion_requests.requirement_id",
                "exclusion_requests.contractor_id",
            );

        /** @var Builder Query to get the requirements for a resource */
        $resourceRequirementsQuery = DB::table("resources")
            ->leftJoin("resource_position", "resource_position.resource_id", "resources.id")
            ->leftJoin("position_requirement", "position_requirement.position_id", "resource_position.position_id")
            ->leftJoin("requirements", function ($join) {
                $join->on("requirements.id", "position_requirement.requirement_id");
            })
            ->select([
                "resources.id as resource_id",
                "requirements.id as requirement_id",
            ])
            ->groupBy("resources.id", "requirements.id");

        $resourcesQuery = DB::table("resources")
            ->leftJoin("contractors", "contractors.id", "resources.contractor_id")
            ->leftJoin("resource_position", "resource_position.resource_id", "resources.id")
            ->leftJoin("positions", "positions.id", "resource_position.position_id")
            ->leftJoin("hiring_organizations", "hiring_organizations.id", "positions.hiring_organization_id")
            ->leftJoinSub($resourceRequirementsQuery, "resource_requirement", function ($join) {
                $join->on("resource_requirement.resource_id", "resources.id");
            })
            ->leftJoin("requirements", "requirements.id", "resource_requirement.requirement_id")
        // Requirement Histories
            ->leftJoinSub($maxRequirementHistorySubQuery, "max_requirement_history_ids", function ($join) {
                $join->on("max_requirement_history_ids.requirement_id", "requirements.id");
                $join->on("max_requirement_history_ids.contractor_id", "contractors.id");
                // $join->on("max_requirement_history_ids.role_id", "roles.id");
                $join->on("max_requirement_history_ids.resource_id", "resources.id");
            })
            ->leftJoin("requirement_histories", "requirement_histories.id", "max_requirement_history_ids.requirement_history_id")
            ->leftJoin("requirement_history_reviews", function ($join) {
                $join->on("requirement_history_reviews.requirement_history_id", "requirement_histories.id");
            })
        // Exclusion Requests
            ->leftJoinSub($maxExclusionRequestQuery, "max_exclusion_request", function ($join) {
                $join->on("max_exclusion_request.requirement_id", "requirements.id");
                $join->on("max_exclusion_request.contractor_id", "contractors.id");
            })
            ->leftJoin("exclusion_requests", function ($join) {
                $join->on("exclusion_requests.id", "max_exclusion_request.exclusion_request_id");
                $join->on("exclusion_requests.status", DB::raw("'approved'"));
            })

            ->select([
                // IDs
                "contractors.id as contractor_id",
                "resources.id as resource_id",
                "positions.id as position_id",
                "hiring_organizations.id as hiring_organization_id",
                "requirements.id as requirement_id",
                "exclusion_requests.id as exclusion_request_id",
                "requirement_histories.id as requirement_history_id",
                "requirement_history_reviews.id as requirement_history_review_id",

                "requirements.count_if_not_approved",

                "exclusion_requests.status as exclusion_request_status",

                // Dates
                "requirement_histories.completion_date",
                DB::raw("$renewalDateColQuery as due_date"),
                DB::raw("$warningDateColQuery as warning_date"),

                // If there is no exclusion
                DB::raw("IF($isActiveColQuery, true, null) as is_active"),

                DB::raw("IF($isPendingColQuery, true, null) as is_pending"),
                DB::raw("IF($isCompletedColQuery,true,null) as is_completed"),

                DB::raw("IF($isCompletedColQuery AND $isActiveColQuery, true, null) as is_completed_and_active"),

                DB::raw("IF($isHiringOrgCompletedColQuery, true, null) as is_hiring_org_completed"),
                DB::raw("IF($isHiringOrgCompletedColQuery AND $isActiveColQuery, true, null) as is_hiring_org_completed_and_active"),
            ]);

        return $resourcesQuery;
    }

    public static function requirementStatusColumnQuery()
    {

        $expiryDateQuery = "(requirement_histories.completion_date + INTERVAL requirements.renewal_period MONTH)";
        $warningDateQuery = "(requirement_histories.completion_date + INTERVAL requirements.renewal_period MONTH - INTERVAL requirements.warning_period DAY)";
        $onTimeQuery = "($expiryDateQuery >= now())";
        $pastDueQuery = "($expiryDateQuery < now())";

        $query = DB::raw("(
            (
                case
                when (`requirement_history_reviews`.`status` = 'declined') then 'past_due'
                when ( NOW() between $warningDateQuery and $expiryDateQuery ) then 'in_warning'
                when ($onTimeQuery) then 'on_time'
                else 'past_due' end
            ) collate utf8mb4_unicode_ci) AS `requirement_status`");

        return $query;
    }

    // view_contractor_resource_overall_compliance
    public static function createViewContractorResourceOverallComplianceQuery()
    {
        $resourcesQuery = self::requirementResourceInfoQuery();

        $isActiveCountCol = "COUNT( requirement_information.is_active )";
        $isCompleteAndActive = "COUNT( requirement_information.is_completed_and_active )";
        $isPendingCountCol = "COUNT( requirement_information.is_pending )";

        $isHiringOrgCompleteAndActiveCol = "COUNT( requirement_information.is_hiring_org_completed_and_active )";

        $countQuery = DB::table(DB::raw("({$resourcesQuery->toSql()}) as requirement_information"))
            ->select([
                "requirement_information.resource_id",
                DB::raw("$isActiveCountCol as requirement_count"),
                DB::raw("$isCompleteAndActive as requirements_completed_count"),
                DB::raw("$isPendingCountCol as pending_requirement_count"),
                DB::raw("ROUND(($isCompleteAndActive / $isActiveCountCol)*100) as compliance"),
                // NOTE: This is the compliance level for the contractor, in the hiring org context (pending approvals = not completed)
                DB::raw("ROUND(($isHiringOrgCompleteAndActiveCol / $isActiveCountCol)*100) as hiring_org_compliance"),
            ])
            ->groupBy("requirement_information.resource_id");

        return $countQuery->toSql();
    }

    // view_contractor_resource_compliance_by_hiring_org
    public static function createViewContractorResourceComplianceByHiringOrgQuery()
    {

        $isActiveCountCol = "COUNT( requirement_information.is_active )";
        $isCompleteAndActive = "COUNT( requirement_information.is_completed_and_active )";
        $isPendingCountCol = "COUNT( requirement_information.is_pending )";

        $isHiringOrgCompleteAndActiveCol = "COUNT( requirement_information.is_hiring_org_completed_and_active )";

        $resourcesQuery = self::requirementResourceInfoQuery();

        $countQuery = DB::table(DB::raw("({$resourcesQuery->toSql()}) as requirement_information"))
            ->leftJoin("resources", "resources.id", "requirement_information.resource_id")
            ->leftJoin("hiring_organizations", "hiring_organizations.id", "requirement_information.hiring_organization_id")
            ->select([
                "requirement_information.resource_id",
                "resources.name as resource_name",
                "resources.contractor_id as contractor_id",
                "requirement_information.hiring_organization_id",
                "hiring_organizations.name as hiring_organization_name",
                DB::raw("$isActiveCountCol as requirement_count"),
                DB::raw("$isCompleteAndActive as requirements_completed_count"),
                DB::raw("FLOOR(($isCompleteAndActive / $isActiveCountCol) * 100) as compliance"),

                // NOTE: This is the compliance level for the contractor, in the hiring org context (pending approvals = not completed)
                DB::raw("ROUND(($isHiringOrgCompleteAndActiveCol / $isActiveCountCol)*100) as hiring_org_compliance"),
            ])
            ->groupBy(
                "requirement_information.resource_id",
                "requirement_information.hiring_organization_id"
            );

        return $countQuery->toSql();
    }

    // view_contractor_resource_compliance_by_hiring_org_position
    public static function createViewContractorResourceComplianceByHiringOrgPositionQuery()
    {
        $isActiveCountCol = "COUNT( requirement_information.is_active )";
        $isCompleteAndActive = "COUNT( requirement_information.is_completed_and_active )";
        $isPendingCountCol = "COUNT( requirement_information.is_pending )";

        $isHiringOrgCompleteAndActiveCol = "COUNT( requirement_information.is_hiring_org_completed_and_active )";

        $resourcesQuery = self::requirementResourceInfoQuery();

        $countQuery = DB::table(DB::raw("({$resourcesQuery->toSql()}) as requirement_information"))
            ->leftJoin("resources", "resources.id", "requirement_information.resource_id")
            ->leftJoin("positions", "positions.id", "requirement_information.position_id")
            ->leftJoin("hiring_organizations", "hiring_organizations.id", "requirement_information.hiring_organization_id")
            ->select([
                "requirement_information.resource_id",
                "resources.contractor_id",
                "requirement_information.hiring_organization_id",
                "hiring_organizations.name as hiring_organization_name",
                "requirement_information.position_id",
                "positions.name as position_name",
                DB::raw("$isActiveCountCol as requirement_count"),
                DB::raw("$isCompleteAndActive as requirements_completed_count"),
                DB::raw("FLOOR(($isCompleteAndActive / $isActiveCountCol) * 100) as compliance"),

                // NOTE: This is the compliance level for the contractor, in the hiring org context (pending approvals = not completed)
                DB::raw("ROUND(($isHiringOrgCompleteAndActiveCol / $isActiveCountCol)*100) as hiring_org_compliance"),
            ]);
        $countQuery
            ->groupBy(
                "requirement_information.resource_id",
                "requirement_information.hiring_organization_id",
                "requirement_information.position_id",
            );

        return $countQuery->toSql();
    }

    // view_contractor_resource_position_requirements
    public static function createViewContractorResourcePositionRequirementsQuery()
    {
        $resourcesQuery = self::requirementResourceInfoQuery();

        $countQuery = DB::table(DB::raw("({$resourcesQuery->toSql()}) as requirement_information"))
            ->leftJoin("resources", "resources.id", "requirement_information.resource_id")
            ->leftJoin("positions", "positions.id", "requirement_information.position_id")
            ->leftJoin("hiring_organizations", "hiring_organizations.id", "requirement_information.hiring_organization_id")
            ->join("requirements", "requirements.id", "requirement_information.requirement_id")
            ->leftJoin("requirement_contents", function ($join) {
                $join->on("requirement_contents.requirement_id", "requirements.id");
                $join->whereIn("requirement_contents.id", function ($query) {
                    $query->select(DB::raw("MAX(requirement_contents.id) as requirement_content_id"))
                        ->from("requirement_contents")
                        ->groupBy("requirement_contents.requirement_id");
                });
            })
            ->leftJoin("requirement_histories", "requirement_histories.id", "requirement_information.requirement_history_id")
            ->leftJoin("exclusion_requests", "exclusion_requests.id", "requirement_information.exclusion_request_id")
            ->leftJoin("requirement_history_reviews", "requirement_history_reviews.id", "requirement_information.requirement_history_review_id")
            ->select([
                // IDs
                "requirement_information.resource_id",
                "requirement_information.contractor_id",
                "requirement_information.hiring_organization_id",
                "requirement_information.position_id",
                "requirement_information.requirement_id",
                "requirement_histories.id as requirement_history_id",
                "requirement_information.exclusion_request_id",

                // Names
                "hiring_organizations.name as hiring_organization_name",
                "positions.name as position_name",
                "requirement_contents.name as requirement_name",
                "resources.name as resource_name",

                // Dates
                "requirements.created_at as requirement_created_at",
                "requirement_histories.completion_date as completion_date",
                "requirement_histories.created_at as created_at",
                DB::raw("(requirement_histories.completion_date + INTERVAL requirements.renewal_period MONTH) as due_date"),
                DB::raw("(requirement_histories.completion_date + INTERVAL requirements.renewal_period MONTH - INTERVAL requirements.warning_period DAY) as warning_date"),

                // Statuses
                "requirement_history_reviews.status as requirement_review_status",
                "exclusion_requests.status as exclusion_status",
                "exclusion_requests.status as exclusion_request_status",

                DB::raw("IF(requirement_information.is_active && !requirement_information.is_completed, true, null) as is_contractor_pending"),

                // Other
                "requirements.count_if_not_approved as requirement_auto_approved",
                "requirements.type as requirement_type",
                "requirements.content_type as requirement_content_type",
                "requirement_contents.lang as lang",
                "requirement_history_reviews.id as requirement_history_review_id",

                self::requirementStatusColumnQuery(),
            ]);

        return $countQuery->toSql();
    }
}
