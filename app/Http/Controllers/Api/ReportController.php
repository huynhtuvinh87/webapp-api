<?php

namespace App\Http\Controllers\Api;

use App\Lib\Traits\ReportControllerTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{

    private $reports = [
        'contractor' => [
            [
                'text' => 'Pending Exclusions',
                'value' => 'reports/contractor/pending-exclusions'
            ]
        ],
        'hiring_organization' => [
            // DEV-809 //
            // Routes are commented as well //
            [
                'text' => 'Corporate Compliance',
                'value' => 'reports/organization/corporate-compliance'
            ],
            [
                'text' => 'Employee Compliance',
                'value' => 'reports/organization/employee-compliance'
            ],
            [
                'text' => 'Pending Employee Invitation',
                'value' => 'reports/organization/pending-employee-invitation'
            ],
            /*
            [
                'text' => 'Contractor Positions',
                'value' => 'reports/organization/positions'
            ],
            [
                'text' => 'Employee Positions',
                'value' => 'reports/organization/employee-positions'
            ],
            [
                'text' => 'Compliance by Position',
                'value' => 'reports/organization/compliance-by-position'
            ],
            */
            [
                'text' => 'Requirements Expiring Within The Next 30 Days',
                'value' => 'reports/organization/requirements-about-expire'
            ],
            [
                'text' => 'Requirements PastDue in The Last 30 Days',
                'value' => 'reports/organization/requirements-past-due'
            ],
            [
                'text' => 'Pending Internal Requirements',
                'value' => 'reports/organization/pending-internal-requirements'
            ],
        ]
    ];

    public function index(Request $request){

        if ($request->user()->role->role === 'employee'){
            return response([
                'error' => 'Not authorized'
            ], 403);
        }

        return response([
            'reports' => $this->reports[$request->user()->role->entity_key]
        ]);

    }

}
