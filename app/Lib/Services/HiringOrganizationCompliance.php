<?php
/**
 * Created by IntelliJ IDEA.
 * User: shane
 * Date: 2019-06-11
 * Time: 11:58
 */

namespace App\Lib\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Traits\CacheTrait;
use App\Models\File;
use Exception;
use App\ViewModels\ViewContractorResourceComplianceByHiringOrg;

class HiringOrganizationCompliance
{
    use CacheTrait;

    /**
     * Get list of contractors an admin can manage
     * @param $role
     * @return \Illuminate\Support\Collection
     */
    public static function getContractors($role)
    {
        if(!isset($role)){
            throw new Exception('Role was undefined');
        }
        if(!isset($role->id)){
            throw new Exception("Role ID was not defined");
        }

        $hiringOrg = $role->company;
        if(!isset($hiringOrg)){
            throw new Exception("Hiring Org could not be found for role");
        }
        if(!isset($hiringOrg->id)){
            throw new Exception("Hiring Org ID could not be found");
        }

        $args = [
            'hiring_organization_id' => $hiringOrg->id,
            'role_id' => $role->id
        ];

        // NOTE: There are 2 queries. First query is a single request, and the second query is with the requests split up.
        // Neither work - need to look into why the script is failing, but its better than before??
        
        $query = "SELECT
            -- u.email,
            -- ho.name AS 'hiring_org_name',
            -- c.name as 'contractor_name',
            DISTINCT c.id as 'contractor_id'
            FROM contractors c
            -- Connecting Hiring Org
            LEFT JOIN contractor_hiring_organization cho ON cho.contractor_id = c.id
            LEFT JOIN hiring_organizations ho ON ho.id = cho.hiring_organization_id
            -- Connecting Contractor Facility
            LEFT JOIN contractor_facility cf ON cf.contractor_id = c.id
            LEFT JOIN facilities f ON f.id = cf.facility_id
                -- Ensuring facility is for hiring org
                AND f.hiring_organization_id = ho.id
            -- Connecting Roles through facility_roles
            LEFT JOIN facility_role fr ON fr.facility_id = f.id
            LEFT JOIN roles r ON r.id = fr.role_id
            -- Connecting User
            LEFT JOIN users u ON u.id = r.user_id
            -- WHERE Clause
            WHERE
                cho.hiring_organization_id = :hiring_organization_id
                AND (
                    r.id = :role_id
                    OR r.id IS NULL
                )
            AND c.id IS NOT NULL
            ORDER BY c.id";

        $query = "WITH contractorsNoFacilities as (
            SELECT
                cho.contractor_id
            FROM contractor_hiring_organization cho
            LEFT JOIN contractor_facility cf ON cf.contractor_id  = cho.contractor_id
            WHERE
            cho.hiring_organization_id = :hiring_organization_id
            AND cf.id IS NULL
        ),
        contractorSameFacilityAsRole as (
            SELECT
                cf.contractor_id
            FROM facility_role fr
            LEFT JOIN facilities f ON f.id = fr.facility_id
            LEFT JOIN contractor_facility cf ON cf.facility_id = fr.facility_id
            WHERE fr.role_id = :role_id
        ),
        contractorsForRole as (
            SELECT
                contractor_id
            from contractorsNoFacilities
            UNION
            SELECT
                contractor_id
            from contractorSameFacilityAsRole
        )
        SELECT
            DISTINCT contractor_id
        FROM contractorsForRole
        WHERE contractor_id IS NOT NULL";

        // If role doesn't have any facilities associated with their account,
        // then return all facilities
        if(sizeof($role->facilities) == 0){
            $args = [
                'hiring_organization_id' => $hiringOrg->id,
            ];

            // Set query to be a list of all contractors for hiring org
            $query = "SELECT
            -- u.email,
            -- ho.name AS 'hiring_org_name',
            -- c.name as 'contractor_name',
            DISTINCT c.id as 'contractor_id'
            FROM contractors c
            -- Connecting Hiring Org
            LEFT JOIN contractor_hiring_organization cho ON cho.contractor_id = c.id
            WHERE
                cho.hiring_organization_id = :hiring_organization_id
            AND c.id IS NOT NULL
            ORDER BY c.id";
        }

        $queryRes = collect(DB::select($query, $args))
            ->filter(function($ids){
                // Removing nulls
                $isSet = isset($ids);
                $isEmpty = $ids == '';
                return $isSet && !$isEmpty;
            });
        return $queryRes->pluck('contractor_id');
    }

    /**
     * Get list of departments an admin works with
     * @param $role
     * @return \Illuminate\Support\Collection
     */
    public static function getDepartments($role)
    {

        return collect(DB::select('select distinct dr.department_id from roles r join department_role dr on dr.role_id = r.id join departments d on d.id = dr.department_id and d.hiring_organization_id = r.entity_id where role_id = :role_id',
            [
                'role_id' => $role->id
            ]))->pluck('department_id');
    }

    public static function getFacilities($role){

        return collect(DB::select('select distinct dr.facility_id from roles r join facility_role dr on dr.role_id = r.id join facilities d on d.id = dr.facility_id and d.hiring_organization_id = r.entity_id where role_id = :role_id',
            [
                'role_id' => $role->id
            ]))->pluck('facility_id');
    }

    /**
     * Get a list of corporate positions for a contractor, with individual compliance
     * @param $role
     * @param $contractor_id
     * @return \Illuminate\Support\Collection
     */
    public static function contractorComplianceByPosition($role, $contractor_id){

		Log::debug(__METHOD__);

        $args = [
            'contractor_id' => $contractor_id,
            'hiring_organization_id' => $role->entity_id
        ];

        $query = 'Select
                p.id,
                p.name,
                count(distinct rq.id) as requirement_count,
                count(distinct rh.id) as requirements_completed_count,
                if(count(distinct rq.id) = 0,100, floor(count(distinct rh.id)/count(distinct rq.id)*100) ) as compliance
            from contractors c
            join contractor_position cp on cp.contractor_id = c.id
            join positions p on p.id = cp.position_id and p.is_active = 1
            join  position_requirement prs on prs.position_id = p.id
            join requirements rq on rq.id = prs.requirement_id and not exists(select 1 from  exclusion_requests ex where ex.requirement_id = rq.id and ex.contractor_id = c.id and ex.status = "approved")
            left outer join requirement_histories rh on rh.requirement_id = rq.id and  rh.contractor_id = c.id and rh.valid = 1
            and rh.id = (select max(id) from requirement_histories rh2 where rh2.requirement_id = rh.requirement_id and rh2.contractor_id = c.id and rh2.valid = 1)
            and date_add(rh.completion_date, INTERVAL rq.renewal_period MONTH) > now()
            and not exists (select 1 from requirement_history_reviews rhr where rhr.requirement_history_id = rh.id and rhr.status = \'declined\')
            and (rq.count_if_not_approved = 1 OR (select count(rhr2.id) from requirement_history_reviews rhr2 where rhr2.requirement_history_id = rh.id and rhr2.status = \'approved\') > 0)
            where p.hiring_organization_id = :hiring_organization_id
            and c.id = :contractor_id
            and p.position_type = "contractor"
            group by p.id;';

        return collect(DB::select($query, $args));

    }
      /**
     * Get a list of corporate positions for a contractor, with individual compliance
     * @param $role
     * @param $contractor_id
     * @return \Illuminate\Support\Collection
     */
    public static function contractorComplianceByResource($role, $contractor_id){

        $prs = ViewContractorResourceComplianceByHiringOrg::where('hiring_organization_id', $role->company->id)
            ->where('contractor_id', $contractor_id)
            ->select([
                "*",
                "hiring_org_compliance as compliance"
            ])
            ->get();
            return $prs;
    }

    /**
     * Get a list of employees for a contractor with their overall compliance
     * @param $role
     * @param $contractor_id
     * @return \Illuminate\Support\Collection
     */
    public static function contractorEmployeeOverallCompliance($role, $contractor_id){

		Log::debug(__METHOD__);

        $args = [
            'contractor_id' => $contractor_id,
            'hiring_organization_id' => $role->entity_id
        ];

        $query = 'Select  er.id as role_id, u.first_name, u.last_name, count(distinct rq.id) as requirement_count, count(distinct rh.id) as requirements_completed_count, if(count(distinct rq.id) = 0,100, floor(count(distinct rh.id)/count(distinct rq.id)*100) ) as compliance
from contractors c
join roles er on er.entity_id = c.id and er.role = \'employee\'
join users u on u.id = er.user_id
join position_role pr on pr.role_id = er.id
join positions p on p.id = pr.position_id and p.is_active = 1
join  position_requirement prs on prs.position_id = p.id
join requirements rq on rq.id = prs.requirement_id and not exists(select 1 from  exclusion_requests ex where ex.requirement_id = rq.id and ex.requester_role_id = er.id and ex.status = "approved")
left outer join requirement_histories rh on rh.requirement_id = rq.id and  rh.role_id = er.id and rh.valid = 1
and rh.id = (select max(id) from requirement_histories rh2 where rh2.requirement_id = rh.requirement_id and rh2.role_id = er.id and rh2.valid = 1)
and date_add(rh.completion_date, INTERVAL rq.renewal_period MONTH) > now()
and not exists (select 1 from requirement_history_reviews rhr where rhr.requirement_history_id = rh.id and rhr.status = \'declined\')
and (rq.count_if_not_approved = 1 OR (select count(rhr2.id) from requirement_history_reviews rhr2 where rhr2.requirement_history_id = rh.id and rhr2.status = \'approved\') > 0)
where p.hiring_organization_id = :hiring_organization_id
and er.deleted_at IS NULL
and p.position_type = "employee"
and c.id = :contractor_id
and u.password IS NOT NULL
group by c.id, er.id;';

        return collect(DB::select($query, $args));

    }

    public static function contractorResourceOverallCompliance($role, $resource){

        Log::debug(__METHOD__);

        $query = 'Select
                        r.name,
                        count(distinct rq.id) as requirement_count,
                        count(distinct rh.id) as requirements_completed_count,
                        if(count(distinct rq.id) = 0, 100, floor(count(distinct rh.id)/ count(distinct rq.id)* 100) ) as compliance
                    from
                        resources r
                    join resource_position rp on
                        rp.resource_id = r.id
                    join positions p on
                        p.id = rp.position_id
                        and p.is_active = 1
                    join position_requirement prs on
                        prs.position_id = p.id
                    join requirements rq on
                        rq.id = prs.requirement_id
                        and not exists(
                        select
                            1
                        from
                            exclusion_requests ex
                        where
                            ex.requirement_id = rq.id
                            and ex.requester_role_id = r.id
                            and ex.status = "approved")
                    left outer join requirement_histories rh on
                        rh.requirement_id = rq.id
                        and rh.role_id = r.id
                        and rh.valid = 1
                        and rh.id = (
                        select
                            max(id)
                        from
                            requirement_histories rh2
                        where
                            rh2.requirement_id = rh.requirement_id
                            and rh2.role_id = r.id
                            and rh2.valid = 1)
                        and date_add(rh.completion_date, INTERVAL rq.renewal_period MONTH) > now()
                        and not exists (
                        select
                            1
                        from
                            requirement_history_reviews rhr
                        where
                            rhr.requirement_history_id = rh.id
                            and rhr.status = \'declined\')
                        and (rq.count_if_not_approved = 1
                        OR (
                        select
                            count(rhr2.id)
                        from
                            requirement_history_reviews rhr2
                        where
                            rhr2.requirement_history_id = rh.id
                            and rhr2.status = \'approved\') > 0)
                    where
                        p.position_type = "resource"
                        and r.id = ' . $resource->id . '
                    group by
                        r.id';

        return collect(DB::select($query));

    }

    /**
     * Get a list of an employee's positions with compliance levels
     * @param $role
     * @param $employee_id
     * @return \Illuminate\Support\Collection
     */
    public static function contractorEmployeeComplianceByPosition($role, $employee_id){

		Log::debug(__METHOD__);

        $args = [
            'employee_id' => $employee_id,
            'hiring_organization_id' => $role->entity_id
        ];

        $query = 'Select  p.id, p.name, count(distinct rq.id) as requirement_count, count(distinct rh.id) as requirements_completed_count, if(count(distinct rq.id) = 0,100, floor(count(distinct rh.id)/count(distinct rq.id)*100) ) as compliance
from roles er
join position_role pr on pr.role_id = er.id
join positions p on p.id = pr.position_id and p.is_active = 1
join  position_requirement prs on prs.position_id = p.id
join requirements rq on rq.id = prs.requirement_id and not exists(select 1 from  exclusion_requests ex where ex.requirement_id = rq.id and ex.requester_role_id = er.id and ex.status = "approved")
left outer join requirement_histories rh on rh.requirement_id = rq.id and  rh.role_id = er.id and rh.valid = 1
and rh.id = (select max(id) from requirement_histories rh2 where rh2.requirement_id = rh.requirement_id and rh2.role_id = er.id and rh2.valid = 1)
and date_add(rh.completion_date, INTERVAL rq.renewal_period MONTH) > now()
and not exists (select 1 from requirement_history_reviews rhr where rhr.requirement_history_id = rh.id and rhr.status = \'declined\')
and (rq.count_if_not_approved = 1 OR (select count(rhr2.id) from requirement_history_reviews rhr2 where rhr2.requirement_history_id = rh.id and rhr2.status = \'approved\') > 0)
where p.hiring_organization_id = :hiring_organization_id
and er.id = :employee_id
group by p.id;';

        return collect(DB::select($query, $args));
    }

    /**
     * Get a list of an role's positions with compliance levels
     * @param $contractor_id
     * @param $resource_id
     * @return \Illuminate\Support\Collection
     */
    public static function contractorResourceComplianceByPosition($contractor_id, $resource_id){

        Log::debug(__METHOD__);

        $args = [
            'contractor_id' => $contractor_id,
            'resource_id' => $resource_id
        ];


        $query = "
            Select
                p.id,
                p.name,
                count(distinct rq.id) as requirement_count,
                count(distinct rh.id) as requirements_completed_count,
                if(count(distinct rq.id) = 0,
                100,
                floor(count(distinct rh.id)/ count(distinct rq.id)* 100) ) as compliance
            FROM
                contractors c
            join resources r on
                r.contractor_id = c.id
            join resource_position rp on
                rp.resource_id = r.id
            join positions p on
                p.id = rp.position_id
            join position_requirement prs on
                prs.position_id = p.id
            join requirements rq on
                rq.id = prs.requirement_id
                and not exists(
                select
                    1
                from
                    exclusion_requests ex
                where
                    ex.requirement_id = rq.id
                    and ex.contractor_id = c.id
                    and ex.status = 'approved')
            left outer join requirement_histories rh on
                rh.requirement_id = rq.id
                and rh.contractor_id = c.id
                and rh.valid = 1
                and rh.resource_id = rp.resource_id
                and rh.id = (
                select
                    max(id)
                from
                    requirement_histories rh2
                where
                    rh2.requirement_id = rh.requirement_id
                    and rh2.contractor_id = c.id
                    and rh2.valid = 1)
                and date_add(rh.completion_date, INTERVAL rq.renewal_period MONTH) > now()
                and not exists (
                select
                    1
                from
                    requirement_history_reviews rhr
                where
                    rhr.requirement_history_id = rh.id
                    and rhr.status = 'declined')
                and (rq.count_if_not_approved = 1
                OR (
                select
                    count(rhr2.id)
                from
                    requirement_history_reviews rhr2
                where
                    rhr2.requirement_history_id = rh.id
                    and rhr2.status = 'approved') > 0)
                and c.id = :contractor_id
                and c.id = :resource_id
                and p.position_type = 'resource'
            group by
                p.id,
                r.name,
                r.id;
        ";

        return collect(DB::select($query, $args));
    }

    public static function getContractorPendingExclusions($role)
    {

        $hiringOrg = $role->company;
        $positionType = 'contractor';
        $filterOptions = null;

        $attachedToPositionQuery = DB::table("position_requirement")
            ->leftJoin('positions', function ($join) {
                $join->on(
                    'position_requirement.position_id',
                    'positions.id'
                );
            })
            //This will account for when the position is inactive
            ->where('positions.is_active', 1)
            ->select('position_requirement.position_id', 'position_requirement.requirement_id');

        // Latest requirement content
        $requirementContentQuery = DB::table("requirement_contents")
        // ->whereIn("requirement_contents.id", DB::raw("(SELECT MAX(id) FROM requirement_contents GROUP BY rc.requirement_id)"));
            ->whereExists(function ($query) {
                $query->select(DB::raw("MAX(id)"))
                    ->from('requirement_contents as rc')
                    ->groupBy("rc.requirement_id")
                    ->whereRaw('rc.id = requirement_contents.id');
            });

        $query = DB::table("exclusion_requests")
            ->leftJoin("requirements", function ($join) {
                $join->on(
                    'requirements.id',
                    'exclusion_requests.requirement_id',
                );
            })
            ->joinSub($attachedToPositionQuery, 'position_requirement_relation', function ($join) {
                $join->on(
                    'position_requirement_relation.requirement_id', 'requirements.id'
                );
            })
            ->leftJoin('positions', function ($join) {
                $join->on(
                    'positions.id',
                    'position_requirement_relation.position_id'
                );
            })
            ->leftJoin("hiring_organizations", function ($join) {
                $join->on(
                    'hiring_organizations.id',
                    'requirements.hiring_organization_id',
                );
            });

        // Attaching contractor and making sure contractor is still connected
        $query->join("contractor_hiring_organization", function ($join) {
            $join->on("contractor_hiring_organization.hiring_organization_id", 'requirements.hiring_organization_id');
            $join->on("contractor_hiring_organization.contractor_id", 'exclusion_requests.contractor_id');
        })
            ->where("contractor_hiring_organization.accepted", DB::raw("true"));

        // Joining Facilities
        $query
            ->leftJoin('facility_position', function ($join) {
                $join->on(
                    'facility_position.position_id',
                    'positions.id'
                );
            })
            ->leftJoin('facilities', function ($join) {
                $join->on(
                    'facilities.id',
                    'facility_position.facility_id'
                );
            });

        // Joining Departments
        $query
            ->leftJoin("department_requirement", function ($join) {
                $join->on(
                    'department_requirement.requirement_id',
                    'requirements.id',
                );
            })
            ->leftJoin("departments", function ($join) {
                $join->on(
                    'departments.id',
                    'department_requirement.department_id'
                );
            });
        
        // Join Contractor Positions on Exclusion Requests
        // If the position is not attached to the contractor, don't show exclusion request
        $query
            ->join("contractor_position", function($join) {
                $join->on(
                'contractor_position.position_id',
                'positions.id')
                ->on('contractor_position.contractor_id', 'exclusion_requests.contractor_id');
            });

        

        // Filtering
        $query
            ->where('hiring_organizations.id', DB::raw("$hiringOrg->id"))
            ->where('exclusion_requests.status', DB::raw("'waiting'"));

        if (isset($positionType)) {
            $query->whereIn('positions.position_type', [
                DB::raw("'contractor'"),
                DB::raw("'resource'"),
            ]);
        }

        $query
            ->groupBy('id')
            ->select([
                'exclusion_requests.id',
                // NOTE: Need for filtering later
                // self::selectColBuilder("facilities.id", "facility_ids"),
                // self::selectColBuilder("positions.id", "position_ids"),
                // self::selectColBuilder("departments.id", "department_ids"),
            ]);

            // NOTE: Commenting this section out, but leaving it in - fitlering for later

        // if (isset($filterOptions)) {
        //     collect(array_keys($filterOptions))
        //         ->each(function ($key) use ($query, $filterOptions) {

        //             // Making sure keys are ordered
        //             $keys = collect($filterOptions[$key])->sort()->toArray();
        //             $filterOptionsStr = join(')%(', $keys);

        //             switch ($key) {
        //                 case 'facility':
        //                     $query->having('facility_ids', "LIKE", DB::raw("'%(" . $filterOptionsStr . ")%'"));
        //                     break;
        //                 case 'position':
        //                     $query->having('position_ids', "LIKE", DB::raw("'%(" . $filterOptionsStr . ")%'"));
        //                     break;
        //                 case 'department':
        //                     $query->having('department_ids', "LIKE", DB::raw("'%(" . $filterOptionsStr . ")%'"));
        //                     break;
        //             }
        //         });
        // }

        $exclusionDetailsQuery = DB::table("exclusion_requests")
            ->joinSub($query, "exclusion_request_ids", function ($join) {
                $join->on('exclusion_request_ids.id', 'exclusion_requests.id');
            })
            ->join("requirements", "requirements.id", "exclusion_requests.requirement_id")
            ->joinSub($requirementContentQuery, "requirement_contents", function ($join) {
                $join->on('requirement_contents.requirement_id', 'exclusion_requests.requirement_id');
            })
            ->join("roles", "roles.id", "exclusion_requests.requester_role_id")
            ->join("users", "users.id", "roles.user_id")
            ->join("contractors", function ($join) {
                $join->on("contractors.id", "roles.entity_id");
                $join->on("roles.entity_key", DB::raw("'contractor'"));
            })
            ->distinct('exclusion_requests.id')
            ->select([
                "exclusion_requests.id as exclusion_request_id",
                "exclusion_requests.contractor_id",
                "exclusion_requests.requirement_id",
                "exclusion_requests.requested_at as created_at",
                "exclusion_requests.status",
                "exclusion_requests.requester_note",
                "contractors.name as contractor_name",
                "requirement_contents.name",
                "requirement_contents.lang",
                "requirements.type",
            ]);

        return $exclusionDetailsQuery->get();

    }

    public static function getEmployeePendingExclusions($role){

        $args = [
            'hiring_organization_id' => $role->entity_id,
            'hiring_org_id_again' => $role->entity_id
        ];

        $dept_filter_lines = [
            '',
            ''
        ];

        $fac_filter_lines = [
            '',
            ''
        ];

        if ($role->role !== 'owner' && $department_ids = implode(',', self::getDepartments($role)->toArray())){

            $dept_filter_lines = [
                'join department_requirement dr on dr.requirement_id = prs.requirement_id',
                "and  dr.department_id in ($department_ids)"
            ];

        }

        if ($role->role !== 'owner' && $facility_ids = implode(',', self::getFacilities($role)->toArray())) {

            $fac_filter_lines = [
                'join contractor_facility cf on cf.contractor_id = c.id',
                "and cf.facility_id in ($facility_ids)"
            ];
        }

        $query = 'Select  distinct u.id as user_id, u.first_name as employee_first, u.last_name as employee_last, c.id as contractor_id, c.name as contractor_name,rq.id as requirement_id,rc.name as requirement_name, rc.lang, rq.type, ex.id as exclusion_request_id, ex.id, ex.requested_at, ex.requested_at as created_at, ex.status, ex.requester_note, er.id as role_id
from contractors c
join contractor_hiring_organization coh on coh.contractor_id = c.id
'.$fac_filter_lines[0].'
join roles er on er.entity_id = c.id and er.role = \'employee\'
join users u on u.id = er.user_id
join position_role pr on pr.role_id = er.id
join positions p on p.id = pr.position_id and p.is_active = 1 and p.hiring_organization_id = coh.hiring_organization_id
join  position_requirement prs on prs.position_id = p.id
'.$dept_filter_lines[0].'
join requirements rq on rq.id = prs.requirement_id
join exclusion_requests ex on ex.requirement_id = rq.id and ex.requester_role_id = er.id  and ex.status = \'waiting\'
join requirement_contents rc on rc.requirement_id = rq.id
where p.hiring_organization_id = :hiring_organization_id
and rq.hiring_organization_id = :hiring_org_id_again
'.$fac_filter_lines[1].'
'.$dept_filter_lines[1].'
and p.position_type = \'employee\'
order by FIELD(rc.lang, "'.App::getLocale().'") desc;';

        return collect(DB::select($query, $args))->unique(function($item){
            return $item->requirement_id.$item->user_id;
        });

    }

    /**
     * Method to generate insight query
     * Takes in the table names for requirements, requirement histories, and req history reviews
     * Returns a string to be included in the
     *
     * @param [type] $requirement_table
     * @param [type] $requirement_history_tabl
     * @param [type] $requirement_history_review_table
     * @return void
     */
    public static function getInsightQuery(
        $requirement_table,
        $requirement_history_table,
		$requirement_history_review_table,
		$exclusion_request_table
    ){
		// Error Checking
		if(is_null($requirement_table) || $requirement_table == ''){
			throw new Exception("Requirement table was not passed to insight query");
		}
		if(is_null($requirement_history_table) || $requirement_history_table == ''){
			throw new Exception("Requirement history table was not passed to insight query");
		}
		if(is_null($requirement_history_review_table) || $requirement_history_review_table == ''){
			throw new Exception("Requirement history review table was not passed to the insight query");
		}
		if(is_null($exclusion_request_table) || $exclusion_request_table == ''){
			throw new Exception("Exclusion request table was not passed to the insight query");
		}

		// TODO: Change insight so that regardless of everything (except for exclusion status), if its been approved, show approved.
        $insightTextQuery = "(
			SELECT
				CASE WHEN $exclusion_request_table.id IS NOT NULL THEN
					CASE WHEN $exclusion_request_table.status = 'approved' THEN
						'Approved Exclusion'
					WHEN $exclusion_request_table.status = 'rejected' THEN
						'Rejected Exclusion'
					WHEN $exclusion_request_table.status = 'waiting' THEN
						'Pending Exclusion'
					END
				# PastDue
				WHEN $requirement_history_table.completion_date + interval $requirement_table.renewal_period MONTH  < now() THEN
					'PastDue'
				# Approved / Rejected
				WHEN $requirement_history_review_table.id IS NOT NULL THEN
					CASE WHEN $requirement_history_review_table.status = 'declined' THEN
						'Rejected'
					WHEN $requirement_history_review_table.status = 'approved' THEN
						'Approved'
					ELSE
						'UNKNOWN'
					END
				ELSE
					# Actioned Requirements
					CASE WHEN $requirement_history_table.id IS NOT NULL THEN
					(
						CASE WHEN $requirement_table.count_if_not_approved = true THEN (
							CASE WHEN type = 'upload' OR type = 'upload_date' OR type = 'form' THEN
								'Uploaded'
							WHEN type = 'test' THEN
								'Passed'
							WHEN type = 'internal' OR type = 'review' THEN
								'Reviewed'
							ELSE
								'Completed'
							END
						) ELSE (
							CASE WHEN $requirement_history_review_table.status IS NULL THEN
								'Pending Approval'
							ELSE
								# Default for upload, upload date, and form
								'UNKNOWN'
								# NOTE: Tests, internal documents, and other types should automatically have count_if_not_approved set to true
								# This should be a red flag if we get to this point
							END
						)
						END
					)
					# Not actioned items
					ELSE
						'Not Actioned'
					END
				END
        )";

        return $insightTextQuery;
    }

	/**
	 * Method to calculate the completion status of a requirement
	 * Checks to see that its still within a valid time period, count if not approved, and if its been reviewed
	 *
	 * @param [type] $requirement_table
	 * @param [type] $requirement_history_table
	 * @param [type] $requirement_history_review_table
	 * @return void
	 */
    public static function getRequirementCompletionQuery(
        $requirement_table,
        $requirement_history_table,
		$requirement_history_review_table,
		$exclusion_request_table
    ){

		// Error Checking
		if(is_null($requirement_table) || $requirement_table == ''){
			throw new Exception("Requirement table was not passed to insight query");
		}
		if(is_null($requirement_history_table) || $requirement_history_table == ''){
			throw new Exception("Requirement history table was not passed to insight query");
		}
		if(is_null($requirement_history_review_table) || $requirement_history_review_table == ''){
			throw new Exception("Requirement history review table was not passed to the insight query");
		}
		if(is_null($exclusion_request_table) || $exclusion_request_table == ''){
			throw new Exception("Exclusion request table was not passed to the insight query");
		}

		// If count_if_not_approved == 0 and no review, show incomplete
        return "IF (
			# Exclusions
			$exclusion_request_table.status = 'approved'
			OR
			(
				# Valid time period
				$requirement_history_table.completion_date + interval $requirement_table.renewal_period MONTH  > now()

				# Completion date was set
				AND $requirement_history_table.completion_date is not null

				AND (
					# Count if not approved
					$requirement_table.count_if_not_approved = true
					OR
					# Reviewed
					$requirement_history_review_table.status = 'approved'
				)
			),

			'Complete',
			'Not Complete'
		)";
    }
}

