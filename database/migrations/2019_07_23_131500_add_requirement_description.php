<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequirementDescription extends Migration
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

            // Add Description column to requirements table
            Schema::table('requirements', function (Blueprint $table) {
                $table->text('description')->nullable();
            });

            // Copied create view from 2019_07_04_134446_add_upload_date_to_requirement_views.php
            // Adding `rq.description as requirement_description
            DB::statement('DROP VIEW IF EXISTS view_contractor_requirements');
            DB::statement('CREATE VIEW view_contractor_requirements AS
        select
          cho.contractor_id AS contractor_id,
          ho.id AS hiring_organization_id,
          ho.name AS hiring_organization_name,
          p.id AS position_id,
          p.name AS position_name,
          rq.id AS requirement_id,
          rq.name AS requirement_name,
          rq.type AS requirement_type,
          rq.description AS requirement_description,
          rq.content_type as requirement_content_type,case
            when current_timestamp() between rh.completion_date + interval rq.renewal_period month - interval rq.warning_period day
            and rh.completion_date + interval rq.renewal_period month then \'in_warning\' when rh.completion_date + interval rq.renewal_period month > current_timestamp() then \'on_time\' else \'past_due\' end AS requirement_status,rh.completion_date AS completion_date,rh.completion_date + interval rq.renewal_period month AS due_date, rh.completion_date + interval rq.renewal_period month - interval rq.warning_period day as warning_date,ex.id AS exclusion_request_id,ex.status AS exclusion_status,rhr.id AS requirement_history_review_id,rhr.status AS requirement_review_status,
        rh.created_at as upload_date
        from contractor_hiring_organization cho
        join positions p on p.is_active = 1 and p.position_type = \'contractor\'  and p.hiring_organization_id = cho.hiring_organization_id
        join contractor_position cp on cp.contractor_id  = cho.contractor_id and cp.position_id = p.id
        join position_requirement prs on prs.position_id = p.id
        join hiring_organizations ho on ho.id = p.hiring_organization_id
        join requirements rq on rq.id = prs.requirement_id
        left outer join exclusion_requests ex on ex.requirement_id = rq.id and ex.contractor_id = cho.contractor_id
        left outer join requirement_histories rh on rh.requirement_id = rq.id and rh.contractor_id = cho.contractor_id  and rh.valid = 1 and rh.id = (select max(rh2.id) from requirement_histories rh2 where rh2.requirement_id = rh.requirement_id and rh2.contractor_id = cho.contractor_id)
        left join requirement_history_reviews rhr on rhr.requirement_history_id = rh.id
        where cho.accepted = 1');

            DB::commit();

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        try {
            DB::beginTransaction();

            // Remove description column from requirements table
            Schema::table('requirements', function (Blueprint $table) {
                $table->dropColumn('description');
            });

            DB::statement('DROP VIEW IF EXISTS view_contractor_requirements');
            DB::statement('CREATE VIEW view_contractor_requirements AS
select cho.contractor_id AS contractor_id,
ho.id AS hiring_organization_id,
ho.name AS hiring_organization_name,
p.id AS position_id,p.name AS position_name,
rq.id AS requirement_id,
rq.name AS requirement_name,rq.type AS requirement_type,rq.content_type as requirement_content_type,case when current_timestamp() between rh.completion_date + interval rq.renewal_period  month - interval rq.warning_period day and rh.completion_date + interval rq.renewal_period month then \'in_warning\' when rh.completion_date + interval rq.renewal_period month > current_timestamp() then \'on_time\' else \'past_due\' end AS requirement_status,rh.completion_date AS completion_date,rh.completion_date + interval rq.renewal_period month AS due_date, rh.completion_date + interval rq.renewal_period month - interval rq.warning_period day as warning_date,ex.id AS exclusion_request_id,ex.status AS exclusion_status,rhr.id AS requirement_history_review_id,rhr.status AS requirement_review_status,
rh.created_at as upload_date
from contractor_hiring_organization cho
join positions p on p.is_active = 1 and p.position_type = \'contractor\'  and p.hiring_organization_id = cho.hiring_organization_id
join contractor_position cp on cp.contractor_id  = cho.contractor_id and cp.position_id = p.id
join position_requirement prs on prs.position_id = p.id
join hiring_organizations ho on ho.id = p.hiring_organization_id
join requirements rq on rq.id = prs.requirement_id
left outer join exclusion_requests ex on ex.requirement_id = rq.id and ex.contractor_id = cho.contractor_id
left outer join requirement_histories rh on rh.requirement_id = rq.id and rh.contractor_id = cho.contractor_id  and rh.valid = 1 and rh.id = (select max(rh2.id) from requirement_histories rh2 where rh2.requirement_id = rh.requirement_id and rh2.contractor_id = cho.contractor_id)
left join requirement_history_reviews rhr on rhr.requirement_history_id = rh.id
where cho.accepted = 1');

            DB::commit();

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
