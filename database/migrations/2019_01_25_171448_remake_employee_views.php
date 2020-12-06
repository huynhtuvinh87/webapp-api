<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemakeEmployeeViews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP VIEW `view_employee_compliance_by_hiring_org`');
        DB::statement('DROP VIEW `view_employee_compliance_by_hiring_org_position`');
        DB::statement('DROP VIEW `view_employee_overall_compliance`');
        DB::statement('DROP VIEW `view_employee_requirements`');

        DB::statement("CREATE VIEW `view_employee_compliance_by_hiring_org` AS select `r`.`user_id` AS `user_id`,`ho`.`id` AS `hiring_organization_id`,`ho`.`name` AS `name`,count(distinct `rq`.`id`) AS `requirement_count`,sum(if(`rh`.`id` is not null,1,0)) AS `requirements_completed_count` from ((((((`roles` `r` join `position_role` `pr` on(`pr`.`role_id` = `r`.`id`)) join `positions` `p` on(`p`.`id` = `pr`.`position_id` and `p`.`is_active` = 1)) join `position_requirement` `prs` on(`prs`.`position_id` = `p`.`id`)) join `hiring_organizations` `ho` on(`ho`.`id` = `p`.`hiring_organization_id`)) join `requirements` `rq` on(`rq`.`id` = `prs`.`requirement_id` and !exists(select 1 from `exclusion_requests` `ex` where `ex`.`requirement_id` = `rq`.`id` and `ex`.`requester_role_id` = `r`.`id`))) left join `requirement_histories` `rh` on(`rh`.`requirement_id` = `rq`.`id` and `rh`.`role_id` = `r`.`id` and `rh`.`id` = (select max(`rh2`.`id`) from `requirement_histories` `rh2` where `rh2`.`requirement_id` = `rh`.`requirement_id` and `rh2`.`role_id` = `r`.`id`) and `rh`.`completion_date` + interval `rq`.`renewal_period` month > current_timestamp() and !exists(select 1 from `requirement_history_reviews` `rhr` where `rhr`.`requirement_histories_id` = `rh`.`id` and `rhr`.`status` = 'declined'))) where `r`.`employee_id` is not null group by `ho`.`id`,`r`.`user_id`");
        DB::statement("CREATE VIEW `view_employee_compliance_by_hiring_org_position` AS select `r`.`user_id` AS `user_id`,`ho`.`id` AS `hiring_organization_id`,`ho`.`name` AS `hiring_organization_name`,`p`.`id` AS `position_id`,`p`.`name` AS `position_name`,count(distinct `rq`.`id`) AS `requirement_count`,sum(if(`rh`.`id` is not null,1,0)) AS `requirements_completed_count` from ((((((`roles` `r` join `position_role` `pr` on(`pr`.`role_id` = `r`.`id`)) join `positions` `p` on(`p`.`id` = `pr`.`position_id` and `p`.`is_active` = 1)) join `position_requirement` `prs` on(`prs`.`position_id` = `p`.`id`)) join `hiring_organizations` `ho` on(`ho`.`id` = `p`.`hiring_organization_id`)) join `requirements` `rq` on(`rq`.`id` = `prs`.`requirement_id` and !exists(select 1 from `exclusion_requests` `ex` where `ex`.`requirement_id` = `rq`.`id` and `ex`.`requester_role_id` = `r`.`id`))) left join `requirement_histories` `rh` on(`rh`.`requirement_id` = `rq`.`id` and `rh`.`role_id` = `r`.`id` and `rh`.`id` = (select max(`rh2`.`id`) from `requirement_histories` `rh2` where `rh2`.`requirement_id` = `rh`.`requirement_id` and `rh2`.`role_id` = `r`.`id`) and `rh`.`completion_date` + interval `rq`.`renewal_period` month > current_timestamp() and !exists(select 1 from `requirement_history_reviews` `rhr` where `rhr`.`requirement_histories_id` = `rh`.`id` and `rhr`.`status` = 'declined'))) where `r`.`employee_id` is not null group by `ho`.`id`,`p`.`id`,`r`.`user_id`");
        DB::statement("CREATE VIEW `view_employee_overall_compliance` AS select `r`.`user_id` AS `user_id`,count(distinct `rq`.`id`) AS `requirement_count`,sum(if(`rh`.`id` is not null,1,0)) AS `requirements_completed_count` from (((((`roles` `r` join `position_role` `pr` on(`pr`.`role_id` = `r`.`id`)) join `positions` `p` on(`p`.`id` = `pr`.`position_id` and `p`.`is_active` = 1)) join `position_requirement` `prs` on(`prs`.`position_id` = `p`.`id`)) join `requirements` `rq` on(`rq`.`id` = `prs`.`requirement_id` and !exists(select 1 from `exclusion_requests` `ex` where `ex`.`requirement_id` = `rq`.`id` and `ex`.`requester_role_id` = `r`.`id`))) left join `requirement_histories` `rh` on(`rh`.`requirement_id` = `rq`.`id` and `rh`.`role_id` = `r`.`id` and `rh`.`id` = (select max(`rh2`.`id`) from `requirement_histories` `rh2` where `rh2`.`requirement_id` = `rh`.`requirement_id` and `rh2`.`role_id` = `r`.`id`) and `rh`.`completion_date` + interval `rq`.`renewal_period` month > current_timestamp() and !exists(select 1 from `requirement_history_reviews` `rhr` where `rhr`.`requirement_histories_id` = `rh`.`id` and `rhr`.`status` = 'declined'))) where `r`.`employee_id` is not null group by `r`.`user_id`");
        DB::statement("CREATE VIEW `view_employee_requirements` AS select `r`.`user_id` AS `user_id`,`ho`.`id` AS `hiring_organization_id`,`ho`.`name` AS `hiring_organization_name`,`p`.`id` AS `position_id`,`p`.`name` AS `position_name`,`rq`.`id` AS `requirement_id`,`rq`.`name` AS `requirement_name`,`rq`.`type` AS `requirement_type`,`rq`.`content_url` AS `requirement_content_url`,`rq`.`content_file` AS `requirement_content_file`,`rq`.`content` AS `requirement_content`,case when `rh`.`completion_date` + interval `rq`.`renewal_period` month > current_timestamp() then 'on_time' when current_timestamp() between `rh`.`completion_date` + interval (`rq`.`renewal_period` - `rq`.`warning_period`) month and `rh`.`completion_date` + interval `rq`.`renewal_period` month then 'in_warning' else 'past_due' end AS `requirement_status`,`rh`.`completion_date` AS `completion_date`,`rh`.`completion_date` + interval `rq`.`renewal_period` month AS `due_date`,`ex`.`id` AS `exclusion_request_id`,`ex`.`status` AS `exclusion_status`,`rhr`.`id` AS `requirement_history_review_id`,`rhr`.`status` AS `requirement_review_status` from ((((((((`roles` `r` join `position_role` `pr` on(`pr`.`role_id` = `r`.`id`)) join `positions` `p` on(`p`.`id` = `pr`.`position_id` and `p`.`is_active` = 1)) join `position_requirement` `prs` on(`prs`.`position_id` = `p`.`id`)) join `hiring_organizations` `ho` on(`ho`.`id` = `p`.`hiring_organization_id`)) join `requirements` `rq` on(`rq`.`id` = `prs`.`requirement_id`)) left join `exclusion_requests` `ex` on(`ex`.`requirement_id` = `rq`.`id` and `ex`.`requester_role_id` = `r`.`id`)) left join `requirement_histories` `rh` on(`rh`.`requirement_id` = `rq`.`id` and `rh`.`role_id` = `r`.`id` and `rh`.`id` = (select max(`rh2`.`id`) from `requirement_histories` `rh2` where `rh2`.`requirement_id` = `rh`.`requirement_id` and `rh2`.`role_id` = `r`.`id`))) left join `requirement_history_reviews` `rhr` on(`rhr`.`requirement_histories_id` = `rh`.`id`)) where `r`.`employee_id` is not null");

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
