<?php

namespace App\Http\Controllers\Api;

/**
 * The island of misfit routes. These had no good place to be
 */

use App\Jobs\SendStripeMetaDeta;
use App\Models\Contractor;
use App\Models\HiringOrganization;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Stripe\Plan;
use Stripe\Stripe;
use Illuminate\Support\Facades\Cache;
use App\Traits\CacheTrait;
use Exception;
use Log;
use App\Models\User;

class ApiController extends Controller
{
    use CacheTrait;

    public function signTcs(Request $request)
    {
        try{
            $user= $request->user();
            // If user and email are not set, throw error
            if(!isset($user) && !isset($request['email'])){
                throw new Exception("No user found");
            }

            // Try to find user by email
            if(!isset($user)){
                $user = User::where('email', $request['email'])->first();
            }

            if(!isset($user)){
                throw new Exception($request['email'] . " could not be found");
            }

            $now = (new \DateTime())->format('Y-m-d H:i:s');
            $user->update(['tc_signed_at' => $now]);
            return response(['message' => 'success']);
        } catch (Exception $e){
            return response(['message' => $e->getMessage()], 400);
        }
    }

    public function searchHiringOrganizations(Request $request){

        if ($request->query('search')){
            return response(['hiring_organizations' => HiringOrganization::where('name', 'like', "%".$request->query('search')."%")->get()]);
        }

        return response(['hiring_organizations' => HiringOrganization::get()]);

    }

    public function getHiringOrganizationFacilities(Request $request, HiringOrganization $hiringOrganization){

        return response([
            'facilities' => $hiringOrganization->facilities()->where("display_on_registration", "=", 1)->orderBy('name')->get()
        ]);

    }

    public function searchContractors(Request $request){

        if ($request->query('search')){
            return response([ 'contractors' => Contractor::where('name', 'like', "%".$request->query('search')."%")->get()]);
        }

        return response(['contractors' => Contractor::get()]);

    }

    public function contactableCompanies(Request $request){

        if ($request->user()->role->entity_key === 'contractor'){

            return response(['hiring_organization' => $request->user()->role->company->hiringOrganizations, 'contractor' => []]);

        }

        return response(['hiring_organization' => [],'contractor' => $request->user()->role->company->contractors]);

    }

    public function contactableUsers(Request $request){


        $response = Cache::tags($this->buildTagsFromRequest($request, ['users', 'roles']))->remember($this->buildKeyFromRequest($request), config('cache.time'), function () use ($request){

            $company_type = $request->get('company_type');
            $company_id = $request->get('company_id');

            if (!$company_type || !$company_id){
                $company_type = $request->user()->role->entity_key;
                $company_id = $request->user()->role->entity_id;
            }

            $entity_key = $request->user()->role->entity_key;
            $entity_id = $request->user()->role->entity_id;

            return response([
                'users' => DB::table('roles')
                    ->join('users', 'users.id', '=', 'roles.user_id')
                    ->join('contractor_hiring_organization', function($query) use ($entity_key, $entity_id, $company_id, $company_type){
                        $query->on('contractor_hiring_organization.'.$entity_key.'_id', '=', 'roles.entity_id')
                            ->where($company_type.'_id', '=', $company_id );
                    })
                    ->where('roles.entity_key', $company_type)
                    ->where('roles.entity_id', $company_id)
                    ->select(
                        'users.id as id',
                        'users.email',
                        'users.first_name',
                        'users.last_name',
                        'roles.entity_key',
                        'roles.entity_id',
                        'roles.role',
                        'users.email_verified_at'
                    )
                    ->distinct('users.email')
                    ->get()
                    ->toArray()
            ]);
        });
        return $response;

    }

    public function stripeCustomWebhook(Request $request){
        $contractor = Contractor::where('stripe_id', $request->get('data')['object']['sources']['data'][0]['customer'])->first();

        if ($contractor){
            SendStripeMetaDeta::dispatch($contractor);
        }

        return response('ok');
    }

    public function describePlans(){
        try{
            Stripe::setApiKey(config('services.stripe.secret'));

            $plans = [];

            foreach(config('services.stripe.plans') as $size => $plan){
                $plans[$size] = Plan::retrieve($plan);
            }

            return response([
                'plans' => $plans
            ]);
        } catch (Exception $e){
            Log::error($e);
            return response(['message' => "Failed to get stripe plans. Please come back later!", 'error' => $e->getMessage()], 400);
        }
    }

    public function searchInvite(Request $request){

        $this->validate($request, [
            'email' => 'required_without:name',
            'name' => 'required_without:email'
        ]);

        $invites = DB::table('contractor_hiring_organization')
            ->join('contractors', 'contractors.id', '=', 'contractor_hiring_organization.contractor_id')
            ->join('hiring_organizations', 'hiring_organizations.id', '=', 'contractor_hiring_organization.hiring_organization_id')
            ->join('roles', 'roles.id', '=', 'contractors.owner_id')
            ->join('users', 'users.id', '=', 'roles.user_id')
            ->where('contractor_hiring_organization.accepted', '=', 0)
            ->whereNotNull('contractor_hiring_organization.invite_code')
            ->where(function($query) use ($request){

                if($request->has('email') && $request->has('name')) {
                    $query->where('users.email', '=', $request->query('email'))
                        ->orWhere('contractors.name', '=', $request->query('name'));

                }

                else if ($request->has('email')){
                    $query->where('users.email', '=', $request->query('email'));
                }

                else if ($request->has('name')){
                     $query->where('contractors.name', '=', $request->query('name'));
                }

            })
            ->select('contractor_hiring_organization.invite_code', 'contractors.name as contractor', 'hiring_organizations.name as organization', 'contractor_hiring_organization.due_date')
            ->get();

        return response([
            'invites' => $invites
        ]);

    }
}
