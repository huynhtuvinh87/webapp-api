<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Requirement;
use App\Models\Role;
use App\Traits\CacheTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HiringOrganizationDepartmentController extends Controller
{
    use CacheTrait;

    public function index(Request $request)
    {
        $role = $request->user()->role;
        $company = $role->company;

        $tags = $this->buildTagsFromRequest($request, [$this->buildTag("Departments", $company->id)]);
        $key = $this->buildKeyFromRequest($request);

        return Cache::tags($tags)
            ->remember($key, config('cache.time'), function () use ($role) {

                $departments = $role->company->departments;
                return response(['departments' => $departments]);

            });
    }

    public function show(Request $request, Department $department){
        if (!$this->hasAccess($request->user(), $department)) {
            return response('not authorized', 403);
        }

        $department->load('admins.user', 'requirements');

        return response(['department' => $department]);
    }

    public function store(Request $request){

        $company = $request->user()->role->company;

        $this->validate($request, [
            'name' => [
                'required',
                'string',
            ],
            'description' => 'string'
        ]);

        if ($company->departments()->where('name', $request->get('name'))->exists()) {
            return response([
                'errors' => [
                    'name' => [
                        __('validation.unique', ['attribute' => 'name'])
                    ]
                ]
            ], 418);
        }

        $department = $company->departments()->create([
            'name' => $request->get('name'),
            'description' => $request->get('description')
        ]);

        $request->user()->role->departments()->attach($department->id);

        $departmentTag = $this->buildTag("Departments", $company->id);
        Cache::tags([$departmentTag])->flush();

        return response(['department' => $department]);

    }

    public function update(Request $request, Department $department){
        $company = $request->user()->role->company;

        $this->validate($request, [
            'name' => [
                'string'
            ],
            'description' => 'string'
        ]);

        if ($company->departments()->where('name', $request->get('name'))->where('id', '!=', $department->id)->exists()) {
            return response([
                'errors' => [
                    'name' => [
                        __('validation.unique', ['attribute' => 'name'])
                    ]
                ]
            ], 418);
        }

        if (!$this->hasAccess($request->user(), $department)) {
            return response('not authorized', 403);
        }

        $department->update($request->all());

        $departmentTag = $this->buildTag("Departments", $company->id);
        Cache::tags([$departmentTag])->flush();

        return response(['department' => $department]);
    }

    public function destroy(Request $request, Department $department){
        $company = $request->user()->role->company;

        if (!$this->hasAccess($request->user(), $department)) {
            return response('not authorized', 403);
        }

        $department->admins()->detach();
        $department->requirements()->detach();

        $department->delete();

        $departmentTag = $this->buildTag("Departments", $company->id);
        Cache::tags([$departmentTag])->flush();

        return response('ok');
    }

    //DEPRECATED
    public function addRoles(Request $request, Department $department){
        if (!$this->hasAccess($request->user(), $department)){
            return response('not authorized', 403);
        }

        $this->validate($request, [
            'role_ids' => 'required|array',
            'role_ids.*' => 'numeric'
        ]);

        foreach ($request->get('role_ids') as $role_id) {
            $role = Role::find($role_id);
            if ($role->entity_key === 'hiring_organization' && $role->entity_id === $department->hiring_organization_id) {
                $role->departments()->sync([$department->id], false);
            }
        }

        Cache::tags($this->buildTagsFromRequest($request))->flush();

        return response('ok');
    }

    //DEPRECATED
    public function  removeRoles(Request $request, Department $department){
        if (!$this->hasAccess($request->user(), $department)){
            return response('not authorized', 403);
        }

        $this->validate($request, [
            'role_ids' => 'required|array',
            'role_ids.*' => 'numeric'
        ]);

        foreach ($request->get('role_ids') as $role_id) {
            $role = Role::find($role_id);
            $role->departments()->detach([$department->id]);
        }

        Cache::tags($this->buildTagsFromRequest($request))->flush();

        return response('ok');
    }
    //DEPRECATED
    public function addRequirements(Request $request, Department $department){
        if (!$this->hasAccess($request->user(), $department)){
            return response('not authorized', 403);
        }

        $this->validate($request, [
            'requirement_ids' => 'required|array',
            'requirement_ids.*' => 'numeric'
        ]);

        foreach ($request->get('requirement_ids') as $requirement_id) {
            $requirement = Requirement::find($requirement_id);
            if ($requirement->hiring_organization_id === $department->hiring_organization_id) {
                $requirement->departments()->sync([$department->id], false);
            }
        }

        Cache::tags($this->buildTagsFromRequest($request))->flush();

        return response('ok');

    }
    //DEPRECATED
    public function removeRequirements(Request $request, Department $department){
        if (!$this->hasAccess($request->user(), $department)){
            return response('not authorized', 403);
        }

        $this->validate($request, [
            'requirement_ids' => 'required|array',
            'requirement_ids.*' => 'numeric'
        ]);

        foreach ($request->get('requirement_ids') as $requirement_id) {
            $requirement = Requirement::find($requirement_id);
            $requirement->departments()->detach([$department->id]);
        }

        Cache::tags($this->buildTagsFromRequest($request))->flush();

        return response('ok');

    }
}
