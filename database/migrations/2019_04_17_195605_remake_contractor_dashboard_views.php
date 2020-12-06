<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemakeContractorDashboardViews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP VIEW `view_contractor_compliance_by_hiring_org`');
        DB::statement('DROP VIEW `view_contractor_compliance_by_hiring_org_position`');
        DB::statement('DROP VIEW `view_contractor_overall_compliance`');
        DB::statement('DROP VIEW `view_contractor_requirements`');

        DB::statement("CREATE VIEW view_contractor_compliance_by_hiring_org AS 
select cho.contractor_id AS contractor_id,ho.id AS hiring_organization_id,ho.name AS name,count(distinct rq.id) AS requirement_count,sum(if(rh.id is not null,1,0)) AS requirements_completed_count 
from contractor_hiring_organization cho
join positions p on p.is_active = 1 and p.position_type = 'contractor'  and p.hiring_organization_id = cho.hiring_organization_id
join contractor_position cp on cp.contractor_id  = cho.contractor_id and cp.position_id = p.id
join position_requirement prs on prs.position_id = p.id 
join hiring_organizations ho on ho.id = cho.hiring_organization_id
join requirements rq on rq.id = prs.requirement_id and !exists(select 1 from exclusion_requests ex where ex.requirement_id = rq.id and ex.contractor_id = cho.contractor_id) 
left outer  join requirement_histories rh on rh.requirement_id = rq.id and rh.contractor_id = cho.contractor_id and rh.id = (select max(rh2.id) from requirement_histories rh2 where rh2.requirement_id = rh.requirement_id and rh2.contractor_id = cho.contractor_id) and rh.completion_date + interval rq.renewal_period month > current_timestamp() and !exists(select 1 from requirement_history_reviews rhr where rhr.requirement_histories_id = rh.id and rhr.status = 'declined')
group by ho.id,cho.contractor_id;");
        DB::statement("CREATE VIEW view_contractor_compliance_by_hiring_org_position AS 
select cho.contractor_id AS contractor_id,ho.id AS hiring_organization_id,ho.name AS hiring_organization_name,p.id AS position_id,p.name AS position_name,count(distinct rq.id) AS requirement_count,sum(if(rh.id is not null,1,0)) AS requirements_completed_count 
from contractor_hiring_organization cho
join positions p on p.is_active = 1 and p.position_type = 'contractor'  and p.hiring_organization_id = cho.hiring_organization_id
join contractor_position cp on cp.contractor_id  = cho.contractor_id and cp.position_id = p.id
join position_requirement prs on prs.position_id = p.id 
join hiring_organizations ho on ho.id = p.hiring_organization_id 
join requirements rq on rq.id = prs.requirement_id and !exists(select 1 from exclusion_requests ex where ex.requirement_id = rq.id and ex.contractor_id = cho.contractor_id) 
left join requirement_histories rh on rh.requirement_id = rq.id and rh.contractor_id = cho.contractor_id 
  and rh.id = (select max(rh2.id) from requirement_histories rh2 where rh2.requirement_id = rh.requirement_id and rh2.contractor_id = cho.contractor_id) and rh.completion_date + interval rq.renewal_period month > current_timestamp() and !exists(select 1 from requirement_history_reviews rhr where rhr.requirement_histories_id = rh.id and rhr.status = 'declined') 
group by ho.id,p.id,cho.contractor_id;");
        DB::statement("CREATE VIEW view_contractor_overall_compliance AS 
select cho.contractor_id AS contractor_id,count(distinct rq.id) AS requirement_count,sum(if(rh.id is not null,1,0)) AS requirements_completed_count 
from contractor_hiring_organization cho 
join positions p on p.is_active = 1 and p.position_type = 'contractor'  and p.hiring_organization_id = cho.hiring_organization_id
join contractor_position cp on cp.contractor_id  = cho.contractor_id and cp.position_id = p.id
join position_requirement prs on prs.position_id = p.id 
join requirements rq on rq.id = prs.requirement_id and !exists(select 1 from exclusion_requests ex where ex.requirement_id = rq.id and ex.contractor_id = cho.contractor_id)
left join requirement_histories rh on rh.requirement_id = rq.id and rh.contractor_id = cho.contractor_id and rh.id = (select max(rh2.id) from requirement_histories rh2 where rh2.requirement_id = rh.requirement_id and rh2.contractor_id = cho.contractor_id and rh.completion_date + interval rq.renewal_period month > current_timestamp() 
  and !exists(select 1 from requirement_history_reviews rhr where rhr.requirement_histories_id = rh.id and rhr.status = 'declined') )
group by cho.contractor_id;");
        DB::statement("CREATE VIEW view_contractor_requirements AS 
select cho.contractor_id AS contractor_id,ho.id AS hiring_organization_id,ho.name AS hiring_organization_name,p.id AS position_id,p.name AS position_name,rq.id AS requirement_id,rq.name AS requirement_name,rq.type AS requirement_type,rq.content_url AS requirement_content_url,rq.content_file AS requirement_content_file,rq.content AS requirement_content,case when rh.completion_date + interval rq.renewal_period month > current_timestamp() then 'on_time' when current_timestamp() between rh.completion_date + interval (rq.renewal_period - rq.warning_period) month and rh.completion_date + interval rq.renewal_period month then 'in_warning' else 'past_due' end AS requirement_status,rh.completion_date AS completion_date,rh.completion_date + interval rq.renewal_period month AS due_date,ex.id AS exclusion_request_id,ex.status AS exclusion_status,rhr.id AS requirement_history_review_id,rhr.status AS requirement_review_status 
from contractor_hiring_organization cho
join positions p on p.is_active = 1 and p.position_type = 'contractor'  and p.hiring_organization_id = cho.hiring_organization_id
join contractor_position cp on cp.contractor_id  = cho.contractor_id and cp.position_id = p.id
join position_requirement prs on prs.position_id = p.id 
join hiring_organizations ho on ho.id = p.hiring_organization_id 
join requirements rq on rq.id = prs.requirement_id
left outer join exclusion_requests ex on ex.requirement_id = rq.id and ex.contractor_id = cho.contractor_id
left outer join requirement_histories rh on rh.requirement_id = rq.id and rh.contractor_id = cho.contractor_id and rh.id = (select max(rh2.id) from requirement_histories rh2 where rh2.requirement_id = rh.requirement_id and rh2.contractor_id = cho.contractor_id) 
left join requirement_history_reviews rhr on rhr.requirement_histories_id = rh.id;");
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
