<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Requirement;
use App\Models\RequirementContent;
use App\Traits\CacheTrait;
use App\Traits\ErrorHandlingTrait;
use App\Traits\FileTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Log;
use App\Models\FileRequirementHistory;
use App\Models\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HiringOrganizationRequirementController extends Controller
{
    use FileTrait;
    use ErrorHandlingTrait;
    use CacheTrait;

    public function index(Request $request){

        $requirements = $request->user()->role->company->requirements;

        $filteredRequirements = $requirements->where('is_visible', true)->values();

        $filteredRequirements->each(function(Requirement $requirement){
            $requirement->loadContent();
		});

        return response([
            'requirements' => $filteredRequirements
        ]);
    }

    public function show(Request $request, Requirement $requirement){
        try{

            if ($requirement->hiring_organization_id === $request->user()->role->entity_id){

                if ($request->has('employee_id')){
                    $requirement->load(['history' => function($query) use ($request, $requirement){

                        $employeeRoleId = $request->get('employee_id');
                        $query
                            ->where('role_id', DB::raw($employeeRoleId))
                            ->latest('id');
                        $query
                            ->leftJoin('requirement_history_reviews', 'requirement_history_reviews.requirement_history_id', '=', 'requirement_histories.id')
                            ->leftJoin('roles', 'roles.id', '=', 'requirement_history_reviews.approver_id')
                            ->leftJoin('users', 'users.id', '=', 'roles.user_id')
                            ->leftJoin("requirements", "requirements.id", "requirement_histories.requirement_id")
                            // ->leftJoin("file_requirement_history", "file_requirement_history.requirement_history_id", "requirement_histories.id")
                            // Redundant checking that the hiring org is both, but its to ensure information cannot be accessed from other accounts.
                            ->where("requirements.hiring_organization_id", $requirement->hiring_organization_id)
                            ->where("requirements.hiring_organization_id", $request->user()->role->entity_id)
                            ->select(
                                'requirement_histories.*',
                                'users.first_name as reviewer_first_name',
                                'users.last_name as reviewer_last_name',
                                'requirement_history_reviews.status as review_status',
                                'requirement_history_reviews.status_at as review_date',
                                'requirement_history_reviews.notes as notes'
                            );

                    }]);

                    $h = $requirement->history;
                    for ($i =0; $i < count($h); $i++) {
                        $reqHist = $requirement->history[$i];
                        $files = $reqHist->files()->get();
                        $h[$i]->file_ids = $files;
                    }

                    // Loading exclusion requests
                    $requirement->load([
                        'exclusionRequest' => function($query) use ($request){
                            $query->where('requester_user.id', $request->query('employee_id'))->latest('id');
                            // Querries for requester information
                            $query
                                ->leftJoin('roles as requester_role', 'requester_role.id', '=', 'exclusion_requests.requester_role_id')
                                ->leftJoin('users as requester_user', 'requester_user.id', '=', 'requester_role.user_id');

                            // Querries for responder information
                            $query
                                ->leftJoin('roles as responder_role', 'responder_role.id', '=', 'exclusion_requests.response_role_id')
                                ->leftJoin('users as responder_user', 'responder_user.id', '=', 'responder_role.user_id');

                            $query->select(
                                'exclusion_requests.*',
                                // Requester details
                                'requester_user.email as requester_email',
                                'requester_user.first_name as requester_first_name',
                                'requester_user.last_name as requester_last_name',
                                // Responder details
                                'responder_user.email as responder_email',
                                'responder_user.first_name as responder_first_name',
                                'responder_user.last_name as responder_last_name'
                            );
                        }
                    ]);
                }

                else if ($request->has('contractor_id') || $request->has('resource_id')){
                    $requirement->load(['history' => function($query) use ($request){
                        $resource_id = $request->resource_id;
                        $query->leftJoin('requirement_history_reviews', 'requirement_history_reviews.requirement_history_id', '=', 'requirement_histories.id')
                        ->leftJoin('roles', 'roles.id', '=', 'requirement_history_reviews.approver_id')
                            ->leftJoin('users', 'users.id', '=', 'roles.user_id')
                            ->select(
                                'requirement_histories.*',
                                'users.first_name as reviewer_first_name',
                                'users.last_name as reviewer_last_name',
                                'requirement_history_reviews.status as review_status',
                                'requirement_history_reviews.status_at as review_date',
                                'requirement_history_reviews.notes as notes'
                            )
                            ->when($request->has('contractor_id'), function($query) use ($request) {
                                $query->where('contractor_id', $request->query('contractor_id'))->latest('id');
                            })
                            ->when($resource_id > 0, function($query) use($resource_id) {
                                $query->where('requirement_histories.resource_id', '=', $resource_id);
                            });

                    }]);
                    $h = $requirement->history;
                    for ($i =0; $i < count($h); $i++) {
                        $reqHist = $requirement->history[$i];
                        $files = $reqHist->files()->get();
                        $h[$i]->file_ids = $files;
                    }

                    // Loading exclusion requests
                    $requirement->load([
                        'exclusionRequest' => function($query) use ($request){
                            $query->where('contractor_id', $request->query('contractor_id'))->latest('id');
                            // Querries for requester information
                            $query
                                ->leftJoin('roles as requester_role', 'requester_role.id', '=', 'exclusion_requests.requester_role_id')
                                ->leftJoin('users as requester_user', 'requester_user.id', '=', 'requester_role.user_id');

                            // Querries for responder information
                            $query
                                ->leftJoin('roles as responder_role', 'responder_role.id', '=', 'exclusion_requests.response_role_id')
                                ->leftJoin('users as responder_user', 'responder_user.id', '=', 'responder_role.user_id');

                            $query->select(
                                'exclusion_requests.*',
                                // Requester details
                                'requester_user.email as requester_email',
                                'requester_user.first_name as requester_first_name',
                                'requester_user.last_name as requester_last_name',
                                // Responder details
                                'responder_user.email as responder_email',
                                'responder_user.first_name as responder_first_name',
                                'responder_user.last_name as responder_last_name'
                            );
                        }
                    ]);

                }

                else {
                    $requirement->load('departments');
                }

                $requirement->loadContent();
                $requirement->loadContents();

                return response([
                    'requirement' => $requirement
                ]);
            }
            return response([
                'message' => 'Not authorized'
            ], 403);
        } catch (Exception $e){
            Log::error(__METHOD__, [
                'error' => $e
            ]);
            return response([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function store(Request $request){
        Log::debug(get_class($this) . "::store()", [
            'user' => $request->user(),
            'request' => $request
        ]);

        // Iterate through all request params and fix null issue
        // FormData passes null as a string; setting to actually be null if null
        foreach($request->all() as $key => $val){
            if($val == 'null'){
                $request->merge([
                    $key => null
                ]);
            }

            // Converting count if not approved to numeric
            if($key=="count_if_not_approved"){
                $request->merge([
                    $key => (int)($val == 'true')
                ]);
            }
        }

        Log::debug("Validating");
        $this->validate($request, [
            'content_name' => 'required|string',
            'content_description' => 'string',
            'type' => 'required|in:upload,review,test,upload_date,internal_document,form',
            'warning_period' => 'required|numeric',
            'renewal_period' => 'required|numeric',
            'notification_email' => 'email',
            'content_type' => 'required|in:text,file,url,none',
            'count_if_not_approved' => 'sometimes|numeric|in:0,1',
            'hard_deadline_date' => 'nullable|date|after:today',
            'content_lang' => 'in:'.implode(',',config('app.available_locales')),
            'content_new_file' => [Rule::requiredIf(function() use ($request){
                return $request->get('content_type') === 'file';
            }), 'file'],
            'content_url' => [Rule::requiredIf(function() use ($request){
                return $request->get('content_type') === 'url';
            }), 'url'],
            'content_text' => [Rule::requiredIf(function() use ($request){
                return $request->get('content_type') === 'text';
            }), 'string', 'min:1'],
            'integration_resource_id' => [Rule::requiredIf(function() use ($request){
                return $request->get('type') == 'test' || $request->get('type') == 'form';
            })],

            // TODO: Fix test validator
            // Expecting the test_id to be the id in tests.
            // FormData converts test_id into a string, which can't be matched.
            // Need to setup a conversion (or just abandon form data because it sucks)
            // Original below

            // 'test_id' => [Rule::requiredIf(function() use ($request){
            //     return $request->get('type') == 'test';
            // }), 'in:tests,id'],
        ]);
        
        $requirement_type = $request->get('type');
        Log::debug("Init changes");
        if ($requirement_type == 'test') { // tests are never auto approved, requirement will be approved if test score is satisfactory
            $request->merge(['count_if_not_approved' => 0]);
        }
        elseif ($requirement_type == 'review'){
            $request->merge(['count_if_not_approved' => 1]);
        } 
        else {
            $request->merge(['count_if_not_approved' => $request->get('count_if_not_approved') ? 1 : 0 ]);
        }

        Log::debug("Creating requirement");
        $requirement = $request->user()->role->company->requirements()->create($request->all());

        Cache::tags($this->buildTagsFromRequest($request))->flush();

        return response(['requirement' => $requirement]);
    }

    //TODO if change content type, require new content, remove old
    public function update(Request $request, Requirement $requirement){
        Log::notice(__METHOD__, [
            'user' => $request->user(),
            'request' => $request
        ]);

        // Iterate through all request params and fix null issue
        // FormData passes null as a string; setting to actually be null if null
        foreach($request->all() as $key => $val){
            if($val == 'null'){
                $request->merge([
                    $key => null
                ]);
            }

            // Converting count if not approved to numeric
            if($key=="count_if_not_approved"){
                $request->merge([
                    $key => (int)($val == 'true')
                ]);
            }
		}

        $this->validate($request, [
            'type' => 'in:upload,review,test,upload_date,internal_document,form',
            'warning_period' => 'numeric',
            'renewal_period' => 'numeric',
            // TODO: Put notification_email and test_id validators back in
            'content_type' => 'required|sometimes|in:text,file,url,none',
            'count_if_not_approved' => 'numeric|in:0,1',
            'hard_deadline_date' => 'nullable|date|after:today',
            'integration_resource_id' => 'nullable'
            // TODO: Put test_id checker back in. Running when update is called to set type = "review" as well
            // 'test_id' => [Rule::requiredIf(function() use ($request){
            //     return $request->get('type') == 'test';
            // }), 'in:tests,id'],
        ]);

        $content_type_change = false;

        if ($request->has('content_type') && $request->get('content_type') != $requirement->content_type){
            $this->validate($request, [
                'content_lang' => 'in:'.implode(',',config('app.available_locales')),
                // TODO: Reimplement validation for content file, url, and text

                // 'content_file' => [Rule::requiredIf(function() use ($request, $requirement){

                //     if ($requirement->content()->whereNotNull('file')->exists()){
                //         return false;
                //     }

                //     return $request->get('content_type') === 'file';
                // }), 'file'],
                // 'content_url' => [Rule::requiredIf(function() use ($request, $requirement){

                //     if ($requirement->content()->whereNotNull('url')->exists()){
                //         return false;
                //     }

                //     return $request->get('content_type') === 'url';
                // }), 'url'],
                // 'content_text' => [Rule::requiredIf(function() use ($request, $requirement){

                //     if ($requirement->content()->whereNotNull('text')->exists()){
                //         return false;
                //     }

                //     return $request->get('content_type') === 'text';
                // }), 'string', 'min:1', 'max:255'],
            ]);

            $content_type_change = true;
        }

        $isUpload = in_array($request->get('type'), ['upload', 'upload_date', 'form']);
        $count = $request->get('count_if_not_approved', $requirement->count_if_not_approved);
        if ($isUpload) {
            $request->merge(['count_if_not_approved' => $count]);
        } elseif ($request->get('type') == 'test') { // tests are never auto approved, requirement will be approved if test score is satisfactory
            $request->merge(['count_if_not_approved' => 0]);
        } else {
            $request->merge(['count_if_not_approved' => 1]);
        }

        $requirement->update($request->all());

        if ($content_type_change && $request->has('content_'.$request->get('content_type'))) {

            // Check if content by lang already exists
            $requirementContent = $requirement->content()
                ->where('lang', $request->get('content_lang', config('app.locale')))
                ->first();
            $contentLangExists = !is_null($requirementContent);

            if(!$contentLangExists){
                $requirementContent = $requirement->content()->create([
                    'lang' => $request->get('content_lang', config('app.locale'))
                ]);
            }

            $requirementContent->file_id = null;

            if ($request->get('content_type') === 'text'){
                $requirementContent->text = $request->get('content_text');
            }

            else if ($request->get('content_type') === 'url'){
                $requirementContent->url = $request->get('content_url');
            }

            else if ($request->get('content_type') === 'file'){

                if ($request->hasFile('file')) {
                    $file = $request->file('file');

                    if (isset($file) && $file != null) {
                        $newFile = $this->createFileFromRequest($request, 'file');
                        $requirementContent->file_id = $newFile->id;
                    }
                }

            }

            $requirementContent->save();

        }

        Cache::tags($this->buildTagsFromRequest($request))->flush();

        $requirement->loadContents();

        return response(['requirement' => $requirement]);

    }

    public function addContent(Request $request, Requirement $requirement){

        $this->validate($request, [
            'name' => 'string|required',
            'description' => 'string',
            'new_file' => Rule::requiredIf(function() use ($requirement){
                return $requirement->content_type === 'file';
            }),
            'url' => Rule::requiredIf(function() use ($requirement){
                return $requirement->content_type === 'url';
            }),
            'text' => Rule::requiredIf(function() use ($requirement){
                return $requirement->content_type === 'text';
            }),
        ]);

        $requirementContent = $requirement->content()->create([
            'lang' => $request->get('lang'),
            'description' => $request->get('description'),
            'name' => $request->get('name')
        ]);

        if ($requirement->content_type === 'text'){
            $requirementContent->text = $request->get('text');
        }

        else if ($requirement->content_type === 'url'){
            $requirementContent->url = $request->get('url');
        }

        else if ($requirement->content_type === 'file'){

            if(!$request->hasFile('new_file')){
                throw new Exception("file was null from request");
            }

            $file = $request->file('new_file');

            if (isset($file) && $file != null) {
                $newFile = $this->createFileFromRequest($request, 'new_file');
                $requirementContent->file_id = $newFile->id;
            }
        }

        $requirementContent->save();

        $requirement->loadContents();

        return response(['requirement' => $requirement]);

    }

    public function getContents(Request $request, Requirement $requirement){

        $content = $requirement->content_type;

        if ($request->has('type') && in_array($request->query('type'), ['file', 'text', 'url'], true)){
            $content = $request->query('type');
        }

        return response([
            'contents' => $requirement->content()->whereNotNull($content)->get()
        ]);

    }

    public function updateContent(Request $request, Requirement $requirement, RequirementContent $requirementContent){

        if (
            $requirement->id !== $requirementContent->requirement_id ||
            $requirement->hiring_organization_id !== $request->user()->role->entity_id
        ){
            return response(['message' => 'not authorized'], 403);
        }

        $this->validate($request, [
            'name' => 'required|string',
            'description' => 'string',
            'url' => Rule::requiredIf(function() use ($requirement){
                return $requirement->content_type === 'url';
            }),
            'text' => Rule::requiredIf(function() use ($requirement){
                return $requirement->content_type === 'text';
            }),
        ]);


        if ($requirement->content_type === 'text'){
            $requirementContent->file_id = null;
            $requirementContent->text = $request->get('text');
        }

        else if ($requirement->content_type === 'url'){
            $requirementContent->file_id = null;
            $requirementContent->url = $request->get('url');
        }

        else if ($requirement->content_type === 'file'){

            if ($request->hasFile('new_file')) {
                $file = $request->file('new_file');

                if (isset($file) && $file != null) {
                    $newFile = $this->createFileFromRequest($request, 'new_file');
                    $requirementContent->file_id = $newFile->id;
                }
            }
        }

        if ($request->get('name')){
            $requirementContent->name = $request->get('name');
        }

        if ($request->get('description')){
            $requirementContent->description = $request->get('description');
        }

        $requirementContent->save();

        $requirement->loadContents();

        return response(['requirement' => $requirement]);

    }

    public function removeContent(Request $request, Requirement $requirement, RequirementContent $requirementContent){
        if (
            $requirement->id !== $requirementContent->requirement_id ||
            $requirement->hiring_organization_id !== $request->user()->role->entity_id ||
            $requirement->content()->whereNotNull($requirement->content_type)->count() < 2
        ){
            return response(['message' => 'not authorized'], 400);
        }

        $requirementContent->delete();

        $requirement->loadContents();

        return response(['requirement' => $requirement]);
    }

    public function getContent(Request $request, Requirement $requirement){

        if (
            $requirement->hiring_organization_id !== $request->user()->role->entity_id
        ){
            return response(['message' => 'not authorized'], 403);
        }

        return response([
            'content' => $requirement->localizedContent()
        ]);

    }

    public function destroy(Request $request, Requirement $requirement){
        if (!$this->belongsToOrg($request->user(), $requirement)){
            return response([
                'message' => 'Not authorized'
            ], 403);
        }

        $requirement->departments()->detach();

        $requirement->positions()->detach();

        $requirement->delete();

        return response(['message' => 'ok']);

    }

    public function addDepartments(Request $request, Requirement $requirement){
        $this->validate($request, [
            'department_ids' => 'required|array|min:1',
            'department_ids.*' => 'numeric'
        ]);

        if (!$this->belongsToOrg($request->user(), $requirement)){
            return response([
                'message' => 'Not authorized'
            ], 403);
        }

        foreach($request->get('department_ids') as $department_id){
            $department = Department::find($department_id);
            if ($department->hiring_organization_id === $request->user()->role->entity_id){
                $requirement->departments()->sync($department_id, false);
            }

        }

        return response(['message' => 'ok']);

    }

    public function removeDepartments(Request $request, Requirement $requirement){
        $this->validate($request, [
            'department_ids' => 'required|array|min:1',
            'department_ids.*' => 'numeric'
        ]);

        if (!$this->belongsToOrg($request->user(), $requirement)){
            return response([
                'message' => 'Not authorized'
            ], 403);
        }

        $requirement->departments()->detach($request->get('department_ids'));

        return response(['message' => 'ok']);
    }

    private function belongsToOrg($user, $requirement)
    {
        return $requirement->hiring_organization_id === $user->role->entity_id;
    }
}
