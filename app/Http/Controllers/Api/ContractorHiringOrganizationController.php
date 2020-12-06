<?php

namespace App\Http\Controllers\Api;

use App\Jobs\AssignAutoAssignPositions;
use App\Models\Contractor;
use App\Models\Facility;
use App\Models\HiringOrganization;
use App\Models\ModuleVisibility;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Traits\CacheTrait;
use Illuminate\Support\Facades\Log;

class ContractorHiringOrganizationController extends Controller
{
    use CacheTrait;
    public function index(Request $request){
        return response($request->user()->role->company->hiringOrganizations);
    }

    public function invites(Request $request){
        return response ($request->user()->role->company->invites);
    }

    public function search(Request $request){

        $limit = $request->query('limit', 20);

       return response(['hiring_organizations' => HiringOrganization::leftJoin('contractor_hiring_organization', function($query) use ($request){
           $query->where('contractor_hiring_organization.contractor_id', '=', $request->user()->role->entity_id)
               ->on('hiring_organizations.id', '=', 'contractor_hiring_organization.hiring_organization_id');
       })->whereNull('contractor_hiring_organization.id')
           ->where(function($query) use ($request){
               if ($request->has('search')){
                   $query->where('hiring_organizations.name', 'like', '%'.$request->get('search').'%');
               }
           })
           ->select('hiring_organizations.id as id', 'contractor_hiring_organization.id as contractor_hiring_organization_id', 'hiring_organizations.name')
           ->limit($limit)
           ->get()]);


    }

    public function addHiringOrganization(Request $request, HiringOrganization $hiringOrganization){

        $this->validate($request, [
            'hiring_organization_facility_ids' => 'array|max:50',
            'hiring_organization_facility_ids.*' => 'numeric'
        ]);

        $contractor = $request->user()->role->company;

        if ($contractor->planCapacity()){
            $contractor->hiringOrganizations()->sync($hiringOrganization->id, false);

            //Attach facilities if exist and belong to hiring organization
            if ($request->get('hiring_organization_facility_ids')){
                foreach($request->get('hiring_organization_facility_ids') as $facility_id){
                    $facility = Facility::find($facility_id);
                    if ($facility->hiring_organization_id === $hiringOrganization->id){
                        $contractor->facilities()->sync($facility_id, false);
                        AssignAutoAssignPositions::dispatchNow($contractor, $facility);

                        Cache::tags([$this->getContractorCacheTag($contractor), $this->getHiringOrgCacheTag($hiringOrganization)])->flush();
                    }
                }
            }

            return response([
                'message' => 'ok',
                'company' => $hiringOrganization
            ]);
        }

        return response([
            'message' => 'requires higher tier plan'
        ], 402);
    }

    public function acceptInvite(Request $request){

        $this->validate($request, [
            'hiring_organization_id' => 'required|numeric'
        ]);

        $contractor = $request->user()->role->company;
        $hiringOrg = HiringOrganization::where('id', $request->get('hiring_organization_id'))->first();

        if ($contractor->planCapacity()){

            $pivot = DB::table('contractor_hiring_organization')
                ->where('hiring_organization_id', $hiringOrg->id)
                ->where('contractor_id', $contractor->id)
                ->update([
                'accepted' => 1,
                'invite_code' => null
            ]);

            $visible_modules = $hiringOrg->moduleVisibility;

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

            Cache::tags([$this->getContractorCacheTag($contractor), $this->getHiringOrgCacheTag($hiringOrg)])->flush();

            return response([
                'message' => 'ok'
            ]);

        }

        return response([
            'message' => 'requires higher tier plan'
        ], 402);

    }

    public function describeInvitation(Request $request){

        $this->validate($request, [
            'invite_code' => 'required|exists:contractor_hiring_organization'
        ]);

        $rel = DB::table('contractor_hiring_organization')
            ->where('invite_code', $request->get('invite_code'))
            ->first();

        if (!$rel){
            return response([
                'valid' => false
            ]);
        }

        $contractor = Contractor::select('name', 'owner_id')->find($rel->contractor_id);
        $role = Role::where("id", $contractor->owner_id)
            ->where("entity_key", "contractor")
            ->where("role", "owner")
            ->first();
        if(!isset($role) && is_null($role)){
            throw new Exception("Contractor owner not found.");
        }
        $user = User::select('email')->find($role->user_id);
        $hiring_organization = HiringOrganization::select('id', 'name', 'address', 'city', 'state', 'postal_code', 'country')->find($rel->hiring_organization_id);

        return response([
            'valid' => true,
            'contractor' => $contractor,
            'user' => $user,
            'hiring_organization' => $hiring_organization
        ]);

    }

    public function detach(Request $request, HiringOrganization $hiringOrganization){
        $request->user()->role->company->hiringOrganizations()->detach($hiringOrganization);

        Cache::tags([$this->getContractorCacheTag($request->user()->role->company), $this->getHiringOrgCacheTag($hiringOrganization)])->flush();

        return response(['message' => 'ok']);
    }
}
