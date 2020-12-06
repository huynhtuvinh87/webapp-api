<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contractor;
use App\Models\HiringOrganization;
use App\Models\User;
use App\Notifications\Integration\TractionGuestContractorNotFoundHost;
use App\Notifications\Integration\TractionGuestNotCompliantEmployee;
use App\Notifications\Integration\TractionGuestNotCompliantHost;
use App\Notifications\Registration\Invite;
use App\Traits\NotificationTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class IntegrationController extends Controller
{

    use NotificationTrait;

    function tractionGuest(Request $request)
    {

        $request->merge($request->query());

        $validator = Validator::make($request->all(), [
            'contractorEmail' => 'required|email',
            'companyEmail' => 'required', // hiring organization email //
            'companyId' => 'required|integer|exists:hiring_organizations,id', // hiring organization id //
        ]);

        // Returning
        if ($validator->fails()) {
            return response($validator->errors(), 400);
        }

        if ($request->get('passKey') !== 'Hiu$87jFt1845(68JJ58') {
            return response("PassKey does not match!", 403);
        }

        $hiring_organization = HiringOrganization::find($request->get('companyId'));
        if (!$hiring_organization) {
            return response("Hiring Organization $request->get('companyId') not found", 404);
        }

        $company_emails = explode(";", $request->get('companyEmail'));

        $user = User::where('email', '=', $request->get('contractorEmail'))->first();

        $isContractorValid = true;

        if ($user) {
            //user found, try find roles and contractor

            $role = $user->roles()->where('entity_key', 'contractor')->first();
            if (!$role) {
                Log::info("Role not found");
                $isContractorValid = false;
            }

            $contractor = $role->company()->first();
            if (!$contractor) {
                Log::info("Contractor not found");
                $isContractorValid = false;
            }

            $subscription = $this->checkContractorSubscriptionStatus($role->company()->first());
            if (!$subscription) {
                Log::info("Contractor does not have a valid subscription in traction guest notification");
                $isContractorValid = false;
            }
        }

        if (!$user || !$isContractorValid) {

            // contractor not found in the system, notifying HO and inviting the contractor

            $response = [
                'compliance' => [
                    'employee' => null,
                    'contractor' => null
                ],
                'compliance_message' => "We couldn't find you in the system, please check your email to learn how to register in Contractor Compliance.",
                'requirements' => [
                    'corporate' => null,
                    'employee' => null,
                ]
            ];

            $data = [
                'contractor_user' => $request->get('contractorEmail'),
                'response' => $response
            ];

            foreach ($company_emails as $company_email) {
                $ho_user = User::where('email', '=', trim($company_email))
                    ->with([
                        'roles' => function ($query) {
                            $query->where('entity_key', '=', 'hiring_organization');
                        }
                    ])
                    ->first();

                if (!$ho_user) {
                    Log::info("HO user not found");
                    continue;
                }

                if ($ho_user->roles[0]->entity_id != $request->get('companyId')) {
                    Log::info("HO user doesnt match companyId");
                    continue;
                }

                // Notify HO users
                $ho_user->notify(new TractionGuestContractorNotFoundHost($data));
            }

            Notification::route('mail', $request->get('contractorEmail'))
                ->notify(new Invite(null, $hiring_organization->name));

            return response([
                'compliance_message' => $response,
            ]);
        }

        // Get Past Due Corporate Requirements
        $corporate_requirements_past_due = DB::table('view_contractor_requirements')
            ->select('requirement_id', 'requirement_name', 'position_name')
            ->where('contractor_id', $contractor->id)
            ->where('hiring_organization_id', $hiring_organization->id)
            ->where('requirement_status', 'past_due')
            ->groupBy('requirement_id', 'requirement_name', 'position_name')
            ->get();

        // Get Past Due Employee Requirements
        $employee_requirements_past_due = DB::table('view_employee_requirements')
            ->select('requirement_id', 'requirement_name', 'position_name')
            ->where('user_id', $user->id)
            ->where('hiring_organization_id', $hiring_organization->id)
            ->where('requirement_status', 'past_due')
            ->groupBy('requirement_id', 'requirement_name', 'position_name')
            ->get();

        $response = [
            'compliance' => [
                'employee' => 0,
                'contractor' => 0
            ],
            'compliance_message' => '',
            'requirements' => [
                'corporate' => '',
                'employee' => '',
            ]
        ];

        try {
            $employee_compliance = $role->complianceByHiringOrganization()
                ->where('hiring_organization_id', $hiring_organization->id)
                ->first()
                ->compliance;
        } catch (Exception $e) {
            $employee_compliance = null;
            Log::error("Error trying find $user->first_name (user id: $user->id) compliance.");
            Log::error($e);
        }

        try {
            $corporate_compliance = $contractor->complianceByHiringOrganization()
                ->where('hiring_organization_id', $hiring_organization->id)
                ->first()
                ->compliance;
        } catch (Exception $e) {
            $corporate_compliance = null;
            Log::error("Error trying find $contractor->name (id: $contractor->id) compliance.");
            Log::error($e);
        }

        if (!is_null($employee_compliance)) {
            $compliant = $employee_compliance == 100 && $corporate_compliance == 100;
        } else {
            $compliant = $corporate_compliance == 100;
        }

        $compliance_message = $compliant ? 'Contractor is Compliant!' : 'Contractor is not 100% Compliant';

        $response = [
            'compliance' => [
                'employee' => $employee_compliance,
                'contractor' => $corporate_compliance
            ],
            'compliance_message' => $compliance_message,
            'requirements' => [
                'corporate' => $corporate_requirements_past_due,
                'employee' => $employee_requirements_past_due,
            ]
        ];

        //Notify Hiring Org, Contractor Owners and Not Compliant Employee

        $non_compliant_employee = User::where('email', $request->get('contractorEmail'))->first();

        $contractor = Contractor::find($role->entity_id);
        $contractor_role_owners = $contractor->owners;

        $data = [
            'hiring_organization' => $hiring_organization,
            'hiring_organization_user' => $request->get('companyEmail'),
            'contractor' => $contractor,
            'contractor_user' => $non_compliant_employee,
            'corporate_requirements' => $corporate_requirements_past_due,
            'employee_requirements' => $employee_requirements_past_due,
            'response' => $response
        ];

        if (!$compliant) {
            // Notify HO users
            foreach ($company_emails as $company_email) {
                $ho_user = User::where('email', '=', trim($company_email))
                    ->with([
                        'roles' => function ($query) {
                            $query->where('entity_key', '=', 'hiring_organization');
                        }
                    ])
                    ->first();

                if (!$ho_user) {
                    Log::info("HO user not found");
                    continue;
                }

                if ($ho_user->roles[0]->entity_id != $request->get('companyId')) {
                    Log::info("HO user doesnt match companyId");
                    continue;
                }

                $ho_user->notify(new TractionGuestNotCompliantHost($data));
            }

            // Notify not compliant employee
            $non_compliant_employee->notify(new TractionGuestNotCompliantEmployee($data));

            //Notify contractor owners
            foreach ($contractor_role_owners as $contractor_owner) {
                $contractor_user = User::find($contractor_owner->user_id);

                // do not notify employee twice if he's a contractor owner that is not compliant
                if ($contractor_user != $non_compliant_employee) {
                    $contractor_user->notify(new TractionGuestNotCompliantEmployee($data));
                }
            }
        }

        return response([
            'compliance_message' => $response,
        ]);

    }
}
