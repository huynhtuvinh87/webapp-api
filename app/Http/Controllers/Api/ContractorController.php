<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateContractorRequest;
use App\Http\Requests\UpdateContractorRequest;
use App\Jobs\SendStripeMetaDeta;
use App\Lib\StripeUtils;
use App\Models\Contractor;
use App\Models\Facility;
use App\Models\HiringOrganization;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use App\Rules\Website;
use App\Notifications\Registration\Welcome;
use Carbon\Carbon;
use App\Traits\AutoAssignTrait;
use App\Traits\ControllerTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Stripe\StripeClient;

class ContractorController extends Controller
{
	use AutoAssignTrait;
    use ControllerTrait;

    public function companies(Request $request)
    {
        return response($request->user()->role->company->hiringOrganizations);
    }

    public function positions(Request $request)
    {
        return response($request->user()->role->company->positions()->where('position_type', 'employee')->get());
    }

    public function facilities(Request $request)
    {
        return response($request->user()->role->company->facilities()->get());
    }

    public function employeePositionsByHiringOrganization(Request $request, $role_id)
    {
        $positions = [];
        try {
            $positions = DB::table('facility_role')
            ->join('roles', 'roles.id', '=', 'facility_role.role_id')
            ->join('facility_position', 'facility_position.facility_id', 'facility_role.facility_id')    
            ->join('facilities', 'facilities.id', '=', 'facility_role.facility_id')
            ->join('positions', 'positions.id', '=', 'facility_position.position_id')
            ->where('positions.position_type', 'employee')
            ->where('positions.is_active', 1)
            ->where('positions.auto_assign', 0)
            ->where('facility_role.role_id', $role_id)
            ->select('positions.name', 'positions.id')
            ->get();
            return response($positions);
        }
        catch(Exception $ex) {
            Log::error(__METHOD__ . ': ' . $ex->getMessage());
            return response(['message' => 'Error finding positions'], 500);
        }
       
    }
    public function employeePositionsByHiringOrganizationByFacility(Request $request, $hiring_organization_id, $facility_id)
    {        
        $employee_positions = DB::table('positions')
            ->leftjoin('facility_position', 'facility_position.position_id', 'positions.id')
            ->leftjoin('facilities', 'facilities.id', 'facility_position.facility_id')
            ->where('position_type', 'employee')
            ->where('positions.is_active', 1)
            ->where('positions.auto_assign', 0)
            ->where('positions.hiring_organization_id', $hiring_organization_id)
            ->where('facilities.id', $facility_id)
        ->select('positions.id', 'positions.name')->get();
        return response($employee_positions);
    }

    public function facilitiesByHiringOrganization(Request $request, $hiring_organization_id)
    {
        return response($request->user()->role->company->facilities()->where('hiring_organization_id', $hiring_organization_id)->get());
    }

    public function contractorPositions(Request $request){
        return response($request->user()->role->company->positions()->where('position_type', 'contractor')->get());
    }
    public function contractorResources(Request $request){
        return response($request->user()->role->company->resources()->get());
    }

    public function contractorPositionsByHiringOrganization(Request $request, $hiring_organization_id){
        return response($request->user()->role->company->positions()->where('hiring_organization_id', $hiring_organization_id)->where('position_type', 'contractor')->get());
    }

    public function resourcePositionsByHiringOrganization($resource_id){
        $resource_positions = [];
        try {
            $resource_positions = DB::table('facility_resource')
            ->join('roles', 'roles.id', '=', 'facility_resource.resource_id')
            ->join('facility_position', 'facility_position.facility_id', 'facility_resource.facility_id')    
            ->join('facilities', 'facilities.id', '=', 'facility_resource.facility_id')
            ->join('positions', 'positions.id', '=', 'facility_position.position_id')
            ->where('positions.position_type', 'resource')
            ->where('positions.is_active', 1)
            ->where('positions.auto_assign', 0)
            ->where('facility_resource.resource_id', $resource_id)
            ->select('positions.name', 'positions.id')
            ->get();
            return response($resource_positions);
        }
        catch(Exception $ex) {
            Log::error(__METHOD__ . ': ' . $ex->getMessage());
            return response(['message' => 'Error finding resource positions'], 500);
        }
    
        return response($resource_positions);
    }

    public function index(Request $request){
        return response($request->user()->role->company()->select([
            'name',
            'phone',
            'address',
            'city',
            'state',
            'country',
            'postal_code',
            'website',
            'wsib_number',
            'logo',
        ])->first());
    }

    public function updateLogo(Request $request){

        $this->validate($request, [
            'logo' => 'required|image'
        ]);

        $company = $request->user()->role->company;

        $path = 'logos/contractors/'.$company->id;

        $name = Storage::putFileAs($path, $request->file('logo'), $company->name.'.'.$request->file('logo')->getClientOriginalExtension(), 'public');

        $company->logo = $name;

        $company->logo_file_name = $company->name;

        $company->logo_file_ext = $request->file('logo')->getClientOriginalExtension();

        $company->save();

        return response(['company' => $company]);

    }

    public function update(UpdateContractorRequest $request){

        $this->validate($request, [
            'name' => 'max:30',
            'phone' => 'max:15',
            'address' => 'string|max:50',
            'city' => 'string|max:30',
            'state' => 'string|max:30',
            'country' => 'string|max:30',
            'postal_code' => 'max:12',
            'website' => [new Website]
        ]);

        $update = $request->user()->role->company()->update($request->only([
            'name',
            'phone',
            'address',
            'city',
            'state',
            'country',
            'postal_code',
            'website',
            'wsib_number',
        ]));

        return response($request->user()->role->company()->first());

    }

    public function store(CreateContractorRequest $request)
    {
        $this->logRequest($request, __METHOD__);

        $logStack = ['daily'];

        try {
            DB::beginTransaction();

            $userProps = [
                'email' => $request->get('email'),
                'password' => bcrypt($request->get('password')),
                'first_name' => $request->get('name'),
                'last_name' => '',
            ];

            $only_local_subscription = false;

            $contractorProps = [
                'name' => $request->get('name'),
                'address' => $request->get('address'),
                'phone' => $request->get('phone'),
                'city' => $request->get('city'),
                'country' => $request->get('country'),
                'state' => $request->get('state'),
                'postal_code' => $request->get('postal_code'),
                'has_subcontractors' => $request->get('hasSubcontractors'),
            ];

            // Contractor was invited by Hiring Organization
            if($request->has('invite_code') && $request->get('invite_code') != ""){

                $rel = DB::table('contractor_hiring_organization')->where('invite_code', $request->get('invite_code'))->first();
                if(!isset($rel)){
                    throw new Exception("This invite code did not find any contractors in our system.");
                }

                // If HO invites contractor, an user would already exist, need to update it
                $user = User::where('email', $request->get('email'))->first();
                $user->fill($userProps);
                if (!$user) {
                    throw new Exception("User not created/found");
                }

                $contractor = Contractor::find($rel->contractor_id);
                $hiring_organization = HiringOrganization::find($rel->hiring_organization_id);

                $contractor->update($contractorProps);
                $contractor->refresh();

            // Contractor organically registered
            } else {

                $user = User::create($userProps);

                $contractorProps['owner_id'] = 0;
                $contractor = Contractor::create($contractorProps);

                $hiring_organization = ($request->get('hiring_organization_id')) ? HiringOrganization::find($request->get('hiring_organization_id')) : null;

                if (!isset($hiring_organization)) {
                    throw new Exception("Hiring Organization not found. Wont be able to attach Contractor to Hiring Org nor send Stripe Metadata.");
                }
            }

            $role = Role::create([
                'user_id' => $user->id,
                'entity_key' => 'contractor',
                'role' => 'owner',
                'entity_id' => $contractor->id,
            ]);

            // Updating role
            $user->update(['current_role_id' => $role->id]);
            $contractor->update(['owner_id' => $user->id]);

            // Creating new access token
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->token;

            // Setup Stripe information
            if ($request->get('coupon')) {
                if (!StripeUtils::isCouponValid($request->get('coupon'))) {
                    DB::rollBack();
                    return response()->json([
                        "error" => "Coupon Code is Invalid", //TODO translate
                    ], 404);
                }

                // NOTE: If total charge is zero, do not create stripe subscription
                // create a stripe customer and a local subscription.
                if (!$request->get('show_credit_card')) {

                    $selected_plan = StripeUtils::getStripePlans(config('services.stripe.plans.small'));

                    switch ($selected_plan->interval) {
                        case 'month':
                            $ends_at = \Carbon\Carbon::now()->addMonth();
                            break;
                        default:
                            $ends_at = \Carbon\Carbon::now()->addYear();
                    }

                    $metadata = [
                        'Contractor Name' => $contractor->name,
                        'Contractor ID' => $contractor->id,
                        'Companies' => $hiring_organization->name,
                        'SignUp Company' => $hiring_organization->name,
                        'SignUp ID' => $hiring_organization->id,
                    ];

                    $contractor
                        ->createAsStripeCustomer([
                            'email' => $request->get('email'),
                            'description' => "100% discount coupon",
                            'metadata' => $metadata,
                        ]);

                    $only_local_subscription = Subscription::create([
                        'name' => 'default',
                        'contractor_id' => $contractor->id,
                        'stripe_id' => 'coupon_stripe_sub',
                        'stripe_plan' => $selected_plan->id,
                        'quantity' => 1,
                        'trial_ends_at' => null,
                        'ends_at' => $ends_at,
                    ]);

                } else {

                    // Register with coupon
                    $contractor
                        ->newSubscription('default', config('services.stripe.plans.small'))
                        ->withCoupon($request->get('coupon'))
                        ->create(
                            $request->get('stripe_token'),
                            [
                                'email' => $request->get('email'),
                            ]
                        );
                }
            } else {
                // Register without coupon
                // TODO: Fix this line here
                $requestStripeToken = $request->get('stripe_token');
                $requestEmail = $request->get('email');
                $paymentMethodId = $request->get('stripe_payment_method');

                $stripe = new StripeClient(config('services.stripe.secret'));
                $paymentMethod = $stripe->paymentMethods->retrieve($paymentMethodId);

                // $contractor->addPaymentMethod($paymentMethod);

                $metadata = [
                    'Contractor Name' => $contractor->name,
                    'Contractor ID' => $contractor->id,
                    'Companies' => $hiring_organization->name,
                    'SignUp Company' => $hiring_organization->name,
                    'SignUp ID' => $hiring_organization->id,
                ];

                $contractor
                    ->createAsStripeCustomer([
                        'email' => $request->get('email'),
                        'metadata' => $metadata,
                    ]);

                $contractor
                    ->newSubscription('default', config('services.stripe.plans.small'))
                    ->create($paymentMethod);
            }

            DB::commit();

            // Attaching contractor to selected hiring org
            $contractor->hiringOrganizations()->attach($hiring_organization->id);
            $this->storeRegistrationFacilities($request, $contractor);

            // Check for modules inherited from Hiring Org
            $this->checkInheritedModules($hiring_organization, $contractor);

            // Send Welcome Email to user
            $user->notify(new Welcome());

            // Do not send Stripe Metadata it was already sent above, besides, it wont find in Stripe the subscription (which is only local)
            if (!$only_local_subscription) {
                SendStripeMetaDeta::dispatchNow($contractor, $hiring_organization);
            }

            return response([
                'status' => 'success',
                'role' => $role,
                'company' => $contractor,
                'user' => $user,
                'access_token' => $token,
            ])
                ->header('Authorization', $token);

        } catch (Stripe\Exception\CardException $e) {
            // Since it's a decline, \Stripe\Exception\CardException will be caught
            DB::rollBack();

            $body = $e->getJsonBody();
            $err = $body['error'];

            $contactBankMessage = "During your payment process, we received an error from your bank. We recommend you contact your bank to confirm this purchase.";
            $verifyCardInfoMessage = "There was an error attempting to process your credit card. Please check your credit card details, and try again.";
            $contactSupportMessage = "There was an error processing your request. Please contact support for further assistance.";

            $isBankError = in_array($err['code'], [
                'card_declined',
            ]);

            $isCardError = in_array($err['code'], [
                'incorrect_cvc',
                'incorrect_number',
                'incorrect_zip',
                'invalid_cvc',
                'invalid_expiry_month',
                'invalid_expiry_year',
                'invalid_number',
            ]);

            // Display contact support message for all other errors
            $errorMessage = $contactSupportMessage;

            // Display contact bank message for errors relating to payment
            if ($isBankError) {
                $errorMessage = $contactBankMessage;
            } else if ($isCardError) {
                // Display verify card info message for errors relating to card validation
                $errorMessage = $verifyCardInfoMessage;
            }

            Log::stack($logStack)->error("Payment Failed", [
                'message' => $e->getMessage(),
                'email' => $user->email,
                'stripe customer id' => $contractor->stripe_id,
                'Environment' => env('APP_NAME'),
            ]);
            Log::error($err);

            return response([
                'status' => 'error',
                'message' => $errorMessage,
            ], 402);

        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly

            DB::rollBack();
            Log::stack($logStack)->error($e);
            return response([
                'status' => 'error',
                'message' => $e->getError()->message,
            ], 402);
        } catch (Stripe\Exception\InvalidRequestException $e) {
            // Invalid parameters were supplied to Stripe's API

            DB::rollBack();
            Log::stack($logStack)->error($e);
            return response([
                'status' => 'error',
                'message' => $e->getError()->message,
            ], 402);
        } catch (\Stripe\Exception\AuthenticationException $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)

            DB::rollBack();
            Log::stack($logStack)->error($e);
            return response([
                'status' => 'error',
                'message' => $e->getError()->message,
            ], 402);
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            // Network communication with Stripe failed

            DB::rollBack();
            Log::stack($logStack)->error($e);
            return response([
                'status' => 'error',
                'message' => $e->getError()->message,
            ], 402);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email

            DB::rollBack();
            Log::stack($logStack)->error($e);
            return response([
                'status' => 'error',
                'message' => $e->getError()->message,
            ], 402);
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            DB::rollBack();
            Log::stack($logStack)->error($e);
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

	/**
	 * Attach facilities to the contractor, and dispatch auto assign
	 *
	 * @param [type] $request
	 * @param [type] $contractor
	 * @return void
	 */
	public function storeRegistrationFacilities($request, $contractor){
        $this->logRequest($request, __METHOD__);
		//Attach facilities if exist and belong to hiring organization
		if ($request->get('hiring_organization_facility_ids')){
			foreach($request->get('hiring_organization_facility_ids') as $facility_id){

				$facility = Facility::find($facility_id);

				if ($facility->hiring_organization_id === (int)$request->get('hiring_organization_id')){
					$contractor->facilities()->sync($facility_id, false);
					$this->autoAssignByContractor($contractor, $facility, null, true);
				}
			}
		}
	}

    /**
     * Check for modules inherited from Hiring Org
     *
     * @param HiringOrganization $hiring_organization
     * @param Contractor $contractor
     */
    private function checkInheritedModules(HiringOrganization $hiring_organization, Contractor $contractor){

        $visible_modules = $hiring_organization->moduleVisibility;

        if($visible_modules) {
            foreach ($visible_modules as $visible_module) {
                $is_module_inherent = $visible_module->module->inherit;

                if ($is_module_inherent) {
                    DB::table('module_visibilities')
                        ->updateOrInsert(
                            ['entity_id' => $contractor->id, 'entity_type' => 'contractor', 'module_id' => $visible_module->module_id,],
                            ['visible' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
                        );
                }
            }
        }

    }
}
