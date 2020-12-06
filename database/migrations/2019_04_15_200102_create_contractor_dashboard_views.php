<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateContractorDashboardViews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP VIEW IF EXISTS `view_contractor_compliance_by_hiring_org`');
        DB::statement('DROP VIEW IF EXISTS `view_contractor_compliance_by_hiring_org_position`');
        DB::statement('DROP VIEW IF EXISTS `view_contractor_overall_compliance`');
        DB::statement('DROP VIEW IF EXISTS `view_contractor_requirements`');

        DB::statement("CREATE VIEW view_contractor_compliance_by_hiring_org AS
select r.entity_id AS contractor_id,ho.id AS hiring_organization_id,ho.name AS name,count(distinct rq.id) AS requirement_count,sum(if(rh.id is not null,1,0)) AS requirements_completed_count
from roles r
join contractor_position cp on cp.contractor_id  = r.entity_id
join positions p on p.id = cp.position_id and p.is_active = 1 and p.position_type = 'contractor'
join position_requirement prs on prs.position_id = p.id
join hiring_organizations ho on ho.id = p.hiring_organization_id
join requirements rq on rq.id = prs.requirement_id and !exists(select 1 from exclusion_requests ex where ex.requirement_id = rq.id and ex.contractor_id = r.entity_id)
left outer  join requirement_histories rh on rh.requirement_id = rq.id and rh.contractor_id = r.entity_id and rh.id = (select max(rh2.id) from requirement_histories rh2 where rh2.requirement_id = rh.requirement_id and rh2.contractor_id = r.entity_id) and rh.completion_date + interval rq.renewal_period month > current_timestamp() and !exists(select 1 from requirement_history_reviews rhr where rhr.requirement_histories_id = rh.id and rhr.status = 'declined')
where r.entity_key = 'contractor' and r.role in ( 'owner','admin') group by ho.id,r.entity_id;");

        DB::statement("CREATE VIEW view_contractor_compliance_by_hiring_org_position AS
select r.entity_id AS contractor_id,ho.id AS hiring_organization_id,ho.name AS hiring_organization_name,p.id AS position_id,p.name AS position_name,count(distinct rq.id) AS requirement_count,sum(if(rh.id is not null,1,0)) AS requirements_completed_count
from roles r
join contractor_position cp on cp.contractor_id  = r.entity_id
join positions p on p.id = cp.position_id and p.is_active = 1
join position_requirement prs on prs.position_id = p.id
join hiring_organizations ho on ho.id = p.hiring_organization_id
join requirements rq on rq.id = prs.requirement_id and !exists(select 1 from exclusion_requests ex where ex.requirement_id = rq.id and ex.contractor_id = r.entity_id)
left join requirement_histories rh on rh.requirement_id = rq.id and rh.contractor_id = r.entity_id
  and rh.id = (select max(rh2.id) from requirement_histories rh2 where rh2.requirement_id = rh.requirement_id and rh2.contractor_id = r.entity_id) and rh.completion_date + interval rq.renewal_period month > current_timestamp() and !exists(select 1 from requirement_history_reviews rhr where rhr.requirement_histories_id = rh.id and rhr.status = 'declined')
where r.entity_key = 'contractor' and r.role in ('admin','owner')  group by ho.id,p.id,r.entity_id;");

        DB::statement("CREATE VIEW view_contractor_overall_compliance AS
select r.entity_id AS contractor_id,count(distinct rq.id) AS requirement_count,sum(if(rh.id is not null,1,0)) AS requirements_completed_count from roles r
join contractor_position cp on cp.contractor_id  = r.entity_id
join positions p on p.id = cp.position_id and p.is_active = 1
join position_requirement prs on prs.position_id = p.id
join requirements rq on rq.id = prs.requirement_id and !exists(select 1 from exclusion_requests ex where ex.requirement_id = rq.id and ex.contractor_id = r.entity_id)
left join requirement_histories rh on rh.requirement_id = rq.id and rh.contractor_id = r.entity_id and rh.id = (select max(rh2.id) from requirement_histories rh2 where rh2.requirement_id = rh.requirement_id and rh2.contractor_id = r.entity_id) and rh.completion_date + interval rq.renewal_period month > current_timestamp()
  and !exists(select 1 from requirement_history_reviews rhr where rhr.requirement_histories_id = rh.id and rhr.status = 'declined')
  where r.entity_key = 'contractor' and r.role in ('admin','owner')  group by r.entity_id;");

        DB::statement("CREATE VIEW view_contractor_requirements AS
select r.entity_id AS contractor_id,ho.id AS hiring_organization_id,ho.name AS hiring_organization_name,p.id AS position_id,p.name AS position_name,rq.id AS requirement_id,rq.name AS requirement_name,rq.type AS requirement_type,rq.content_url AS requirement_content_url,rq.content_file AS requirement_content_file,rq.content AS requirement_content,case when rh.completion_date + interval rq.renewal_period month > current_timestamp() then 'on_time' when current_timestamp() between rh.completion_date + interval (rq.renewal_period - rq.warning_period) month and rh.completion_date + interval rq.renewal_period month then 'in_warning' else 'past_due' end AS requirement_status,rh.completion_date AS completion_date,rh.completion_date + interval rq.renewal_period month AS due_date,ex.id AS exclusion_request_id,ex.status AS exclusion_status,rhr.id AS requirement_history_review_id,rhr.status AS requirement_review_status
from roles r
join contractor_position cp on cp.contractor_id  = r.entity_id
join positions p on p.id = cp.position_id and p.is_active = 1  and p.position_type = 'contractor'
join position_requirement prs on prs.position_id = p.id
join hiring_organizations ho on ho.id = p.hiring_organization_id
join requirements rq on rq.id = prs.requirement_id
left outer join exclusion_requests ex on ex.requirement_id = rq.id and ex.contractor_id = r.entity_id
left outer join requirement_histories rh on rh.requirement_id = rq.id and rh.contractor_id = r.entity_id and rh.id = (select max(rh2.id) from requirement_histories rh2 where rh2.requirement_id = rh.requirement_id and rh2.contractor_id = r.entity_id)
left join requirement_history_reviews rhr on rhr.requirement_histories_id = rh.id
where r.entity_key = 'contractor' and r.role in ('admin','owner');");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contractor_dashboard_views');
    }
}
