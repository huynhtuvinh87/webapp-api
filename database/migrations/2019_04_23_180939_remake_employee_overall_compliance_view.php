<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemakeEmployeeOverallComplianceView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP VIEW `view_employee_overall_compliance`');
        DB::statement("CREATE VIEW `view_employee_overall_compliance` AS select `r`.`id` AS role_id, `r`.`entity_key`, `r`.`entity_id`, `r`.`user_id` AS `user_id`,count(distinct `rq`.`id`) AS `requirement_count`,sum(if(`rh`.`id` is not null,1,0)) AS `requirements_completed_count` from (((((`roles` `r` join `position_role` `pr` on(`pr`.`role_id` = `r`.`id`)) join `positions` `p` on(`p`.`id` = `pr`.`position_id` and `p`.`is_active` = 1)) join `position_requirement` `prs` on(`prs`.`position_id` = `p`.`id`)) join `requirements` `rq` on(`rq`.`id` = `prs`.`requirement_id` and !exists(select 1 from `exclusion_requests` `ex` where `ex`.`requirement_id` = `rq`.`id` and `ex`.`requester_role_id` = `r`.`id`))) left join `requirement_histories` `rh` on(`rh`.`requirement_id` = `rq`.`id` and `rh`.`role_id` = `r`.`id` and `rh`.`id` = (select max(`rh2`.`id`) from `requirement_histories` `rh2` where `rh2`.`requirement_id` = `rh`.`requirement_id` and `rh2`.`role_id` = `r`.`id`) and `rh`.`completion_date` + interval `rq`.`renewal_period` month > current_timestamp() and !exists(select 1 from `requirement_history_reviews` `rhr` where `rhr`.`requirement_histories_id` = `rh`.`id` and `rhr`.`status` = 'declined'))) where `r`.`user_id` is not null group by `r`.`user_id`, `r`.`id`, `r`.`entity_id`, `r`.`entity_key`");

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
