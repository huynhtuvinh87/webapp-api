<?php

namespace App\Http\Controllers\Api;

use App\Lib\Traits\ReportControllerTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ContractorReportController extends Controller
{
    use ReportControllerTrait;


    public function pendingExclusions(Request $request){

        $headers = [
            [
                "text" => "Requirement Name",
                "value" => "requirement_name"
            ],
            [
                "text" => "Name",
                "value" => "name"
            ],
            [
                "text" => "Requirement Type",
                "value" => "type"
            ]
        ];

        $employee_exclusions = collect(DB::table('exclusion_requests')
            ->join('requirements', 'requirements.id', '=', 'exclusion_requests.requirement_id')
            ->join('roles', 'exclusion_requests.requester_role_id', '=', 'roles.id')
            ->join('users', 'users.id', '=', 'roles.user_id')
            ->where('roles.role', '=', 'employee')
            ->where('exclusion_requests.contractor_id', '=', $request->user()->role->entity_id)
            ->where('exclusion_requests.status', '=', 'waiting')
            ->select(DB::raw("CONCAT(users.first_name, ' ', users.last_name) as name, requirements.name as requirement_name, 'employee' as type"))
            ->get());

        $contractor_exclusions = collect(DB::table('exclusion_requests')
            ->join('requirements', 'requirements.id', '=', 'exclusion_requests.requirement_id')
            ->join('roles', 'exclusion_requests.requester_role_id', '=', 'roles.id')
            ->join('contractors', 'contractors.id', '=', 'exclusion_requests.contractor_id')
            ->where('roles.role', '!=', 'employee')
            ->where('exclusion_requests.contractor_id', '=', $request->user()->role->entity_id)
            ->where('exclusion_requests.status', '=', 'waiting')
            ->select(DB::raw("contractors.name as name, requirements.name as requirement_name, 'contractor' as type"))
            ->get());

        $data = $employee_exclusions->merge($contractor_exclusions);

        return $this->report($request, $data, $headers);

    }

}
