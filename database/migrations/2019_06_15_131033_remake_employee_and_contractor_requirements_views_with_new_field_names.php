<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemakeEmployeeAndContractorRequirementsViewsWithNewFieldNames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP VIEW `view_employee_requirements`');
        DB::statement('DROP VIEW `view_contractor_requirements`');

        //VIEW contractor_requirements
        DB::statement('CREATE VIEW view_contractor_requirements AS 
select cho.contractor_id AS contractor_id,ho.id AS hiring_organization_id,ho.name AS hiring_organization_name,p.id AS position_id,p.name AS position_name,rq.id AS requirement_id,rq.name AS requirement_name,rq.type AS requirement_type,rq.content_type as requirement_content_type,case when rh.completion_date + interval rq.renewal_period month > current_timestamp() then \'on_time\' when current_timestamp() between rh.completion_date + interval (rq.renewal_period - rq.warning_period) month and rh.completion_date + interval rq.renewal_period month then \'in_warning\' else \'past_due\' end AS requirement_status,rh.completion_date AS completion_date,rh.completion_date + interval rq.renewal_period month AS due_date,ex.id AS exclusion_request_id,ex.status AS exclusion_status,rhr.id AS requirement_history_review_id,rhr.status AS requirement_review_status 
from contractor_hiring_organization cho
join positions p on p.is_active = 1 and p.position_type = \'contractor\'  and p.hiring_organization_id = cho.hiring_organization_id
join contractor_position cp on cp.contractor_id  = cho.contractor_id and cp.position_id = p.id
join position_requirement prs on prs.position_id = p.id 
join hiring_organizations ho on ho.id = p.hiring_organization_id 
join requirements rq on rq.id = prs.requirement_id
left outer join exclusion_requests ex on ex.requirement_id = rq.id and ex.contractor_id = cho.contractor_id
left outer join requirement_histories rh on rh.requirement_id = rq.id and rh.contractor_id = cho.contractor_id  and rh.valid = 1 and rh.id = (select max(rh2.id) from requirement_histories rh2 where rh2.requirement_id = rh.requirement_id and rh2.contractor_id = cho.contractor_id) 
left join requirement_history_reviews rhr on rhr.requirement_history_id = rh.id
where cho.accepted = 1
group by ho.id,p.id,cho.contractor_id,rq.id,rh.completion_date,exclusion_request_id,rhr.id order by cho.contractor_id;');

        //VIEW employee_requirements
        DB::statement('CREATE VIEW view_employee_requirements AS select r.id as role_id, r.user_id AS user_id,ho.id AS hiring_organization_id,ho.name AS hiring_organization_name,p.id AS position_id,p.name AS position_name
,rq.id AS requirement_id,rq.name AS requirement_name,rq.type AS requirement_type,rq.content_type as requirement_content_type,case when rh.completion_date + interval rq.renewal_period month > current_timestamp() then \'on_time\' when current_timestamp() between rh.completion_date + interval (rq.renewal_period - rq.warning_period) month and rh.completion_date + interval rq.renewal_period month then \'in_warning\' else \'past_due\' end AS requirement_status
,rh.completion_date AS completion_date,rh.completion_date + interval rq.renewal_period month AS due_date,ex.id AS exclusion_request_id,ex.status AS exclusion_status
,rhr.id AS requirement_history_review_id,rhr.status AS requirement_review_status, rhr.notes
from roles r join position_role pr on pr.role_id = r.id 
join positions p on p.id = pr.position_id and p.is_active = 1 
join position_requirement prs on prs.position_id = p.id 
join hiring_organizations ho on ho.id = p.hiring_organization_id 
join requirements rq on rq.id = prs.requirement_id 
join contractor_hiring_organization cho on cho.contractor_id = r.entity_id and cho.hiring_organization_id = rq.hiring_organization_id and cho.accepted = 1  
left join exclusion_requests ex on ex.requirement_id = rq.id and ex.requester_role_id = r.id 
left join requirement_histories rh on rh.requirement_id = rq.id and rh.role_id = r.id and rh.valid = 1 and rh.id = (select max(rh2.id) from requirement_histories rh2 where rh2.requirement_id = rh.requirement_id and rh2.role_id = r.id)
left join requirement_history_reviews rhr on rhr.requirement_history_id = rh.id and rhr.id = (select max(rhr2.id) from requirement_history_reviews rhr2 where rhr2.requirement_history_id = rhr.requirement_history_id)
where r.user_id is not null and r.deleted_at is null;');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
