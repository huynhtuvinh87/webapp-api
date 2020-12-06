<?php

namespace App\Http\Controllers\Api;

use App\Models\Facility;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Traits\CacheTrait;

class HiringOrganizationFacilityController extends Controller
{
    use CacheTrait;

    public function index(Request $request){

        return response(['facilities' => $request->user()->role->company->facilities()->orderBy('name')->get()]);

    }

    public function getPositions(Request $request){

        $this->validate($request, [
            'facility_ids' => 'required|array',
            ''
        ]);

    }

    public function show(Request $request, Facility $facility){
        if (!$this->hasAccess($request->user(), $facility)){
            return response('not authorized', 403);
        }

        $facility->load('admins.user', 'positions', 'contractors');

        return response(['facility' => $facility]);
    }

    public function store(Request $request){

        $company = $request->user()->role->company;

        $this->validate($request, [
            'name' => [
                'required',
                'string',
            ],
            'description' => 'string',
            'notification_email' => 'email',
            'display_on_registration' => 'sometimes|required|boolean'
        ]);

        if ($company->facilities()->where('name', $request->get('name'))->exists()){
            return response([
                'errors' => [
                    'name' => [
                        __('validation.unique', ['attribute' => 'name'])
                    ]
                ]
            ], 418);
        }

        $facility = $company->facilities()->create([
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'notification_email' => $request->get('notification_email'),
            'display_on_registration' => $request->has('display_on_registration') ? 1 : 0,
        ]);

        $request->user()->role->facilities()->attach($facility->id);

        Cache::tags([$this->getCompanyCacheTag($request->user()->role), $this->getHiringOrgCacheTag($company)])->flush();

        return response(['facility' => $facility]);

    }

    public function update(Request $request, Facility $facility){
        $company = $request->user()->role->company;

        $this->validate($request, [
            'name' => [
                'string'
            ],
            'description' => 'string',
            'notification_email' => 'email',
            'display_on_registration' => 'sometimes|required|boolean'
        ]);

        if ($company->facilities()->where('name', $request->get('name'))->where('id', '!=', $facility->id)->exists()){
            return response([
                'errors' => [
                    'name' => [
                        __('validation.unique', ['attribute' => 'name'])
                    ]
                ]
            ], 418);
        }

        if (!$this->hasAccess($request->user(), $facility)){
            return response('not authorized', 403);
        }

        $facility->update([
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'notification_email' => $request->get('notification_email'),
            'display_on_registration' => $request->has('display_on_registration') ? 1 : 0,
        ]);

        Cache::tags([$this->getCompanyCacheTag($request->user()->role), $this->getHiringOrgCacheTag($company)])->flush();

        return response(['facility' => $facility]);
    }

    public function destroy(Request $request, Facility $facility){
        if (!$this->hasAccess($request->user(), $facility)){
            return response('not authorized', 403);
        }

        $facility->admins()->detach();
        $facility->positions()->detach();
        $facility->contractors()->detach();

        $facility->delete();

        Cache::tags([$this->getCompanyCacheTag($request->user()->role), $this->getHiringOrgCacheTag($request->user()->role->company)])->flush();

        return response('ok');
    }


    private function belongsToOrg($user, $facility){
        return $user->role->entity_key === 'hiring_organization' && $facility->hiring_organization_id === $user->role->entity_id;
    }


    /**
     * DEPRECATED this function used to determine if admin was assigned to facility, which is no longer a requirement
     * @param $user
     * @param $facility
     * @return bool
     */
    private function hasAccess($user, $facility){
        return $this->belongsToOrg($user, $facility);
    }
}
