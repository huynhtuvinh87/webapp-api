<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contractor;
use App\Models\Folder;
use App\Models\HiringOrganization;
use App\Models\Role;
use App\Traits\FileTrait;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;

class FolderController extends Controller
{
    use RequestTrait;
    use FileTrait;

    /**
     * @param  Request  $request
     * @param $contractor_id
     * @return $this->read($request, $contractor_id);
     */
    public function create(Request $request, $contractor_id)
    {
        try {

            $request_data = $request->validate([
                'name' => 'required|string|min:2',
                'department_id' => 'required|integer|exists:departments,id',
            ]);
            $request_data['hiring_organization_id'] = $request->user()->role->entity_id;

            $folder = Folder::create($request_data);

            $folder->departments()->attach($request->department_id);

            $folder->contractors()->attach($contractor_id);

            return $this->read($request, $contractor_id);

        } catch (QueryException $e) {
            if ($e->getCode() == 1062 || $e->getCode() == 23000) {
                return 'Duplicated folder name.';
            } else {
                return $this->handleError($e, 422);
            }
        }
    }

    /**
     * @param  Request  $request
     * @param $contractor_id
     * @return $this->read($request, $contractor_id);
     */
    public function read(Request $request, $contractor_id)
    {
        try {
            if (!$contractor_id) {
                return response('Contractor not found', 400);
            }

            $contractor = Contractor::find($contractor_id);

            if(!$contractor->folders()->first()){

                $folders_to_attach = Folder::where('hiring_organization_id', $request->user()->role->entity_id)->get();

                foreach ($folders_to_attach as $folder){
                    $folder->contractors()->attach($contractor->id);
                }
            }

            $departments = $request->user()->role->departments->pluck('id')->toArray();
            $departments = implode(',', $departments);

            $folders = DB::table('folders')
                ->join('contractor_folder', 'folders.id', '=', 'contractor_folder.folder_id')
                ->join('department_folder', 'folders.id', '=', 'department_folder.folder_id')
                ->where('folders.hiring_organization_id', $request->user()->role->entity_id)
                ->where('contractor_folder.contractor_id', $contractor_id)
                ->whereNull('folders.deleted_at')
                ->select(
                    'folders.id',
                    'folders.name as folder_name',
                    'folders.hiring_organization_id',
                    'contractor_folder.contractor_id',
                    'department_folder.department_id',
                    'folders.created_at',
                    'folders.updated_at',
                    DB::raw('IF(FIND_IN_SET(department_folder.department_id, "'.$departments.'"), 1, 0) as can_upload')
                )
                ->get();

            return response(['folders' => $folders]);

        } catch (Exception $e) {
            return $this->handleError($e, $e->getCode());
        }
    }

    /**
     * @param  Request  $request
     * @param  Folder  $folder
     * @return $this->read($request, $contractor_id);
     */
    public function update(Request $request, Folder $folder)
    {
        try {
            $this->checkPermissions($request, $folder);

            $request->validate(['name' => 'required|string|min:2']);

            $folder->update($request->all());

            $folder_contractor = $folder->contractors()->first();

            return $this->read($request, $folder_contractor->id);

        } catch (Exception $e) {
            return $this->handleError($e, 422);
        }
    }

    /**
     * @param  Request  $request
     * @param  Folder  $folder
     * @return $this->read($request, $contractor_id);
     */
    public function delete(Request $request, Folder $folder)
    {
        try {
            $this->checkPermissions($request, $folder);

            $folder_contractor = $folder->contractors()->first();

            $folder->delete();

            return $this->read($request, $folder_contractor->id);

        } catch (Exception $e) {
            return $this->handleError($e, 500);
        }
    }

    /**
     * Upload files into folder
     *
     * @param  Request  $request
     * @param  Folder  $folder
     * @return $this->read_content($folder)
     * @throws Exception
     */
    public function upload_file(Request $request, Folder $folder)
    {
        try {
            $this->checkPermissions($request, $folder);

            $file = $this->createFileFromRequest($request, 'file');
            $file['uploader'] = $request->user()->email;

            $folder->files()->attach($file->id, ['contractor_id' => $request->contractor_id]);

            return response(['file' => $file]);

        } catch (Exception $e) {
            return $this->handleError($e, 500);
        }
    }

    /**
     * @param  Request  $request
     * @param  Folder  $folder
     * @return
     *     {
     *          "id": int,
     *          "name": string,
     *          "path": string,
     *          "ext": string,
     *          "role_id": int,
     *          "ip": string,
     *          "disk": string,
     *          "visibility": string,
     *          "updated_at": datetime,
     *          "created_at": datetime,
     *          "deleted_at": datetime,
     *          "storage_size": null,
     *          "fullPath": string,
     *          "size": int,
     *          "pivot": {
     *              "folder_id": int,
     *              "file_id": int,
     *              "created_at": datetime,
     *              "updated_at": datetime
     *          }
     *     }
     *
     */
    public function read_content(Request $request, Folder $folder)
    {
        try {

            $files = $folder->files()->where('contractor_id', $request->contractor_id)->get();
            foreach ($files as $file) {
                $role = Role::where('id', $file->role_id)->with('user')->first();

                $file['folder'] = $folder->name;
                $file['uploader'] = $role->user->email;
            }

            return response(['files' => $files]);

        } catch (Exception $e) {
            return $this->handleError($e, 500);
        }
    }

    /**
     * Checks HO folders
     * @param  Request  $request
     * @return bool
     */
    public function count_hiring_organization_folders(Request $request)
    {
        try {
            $role = $request->user()->role;

            if($role->entity_key != 'hiring_organization'){
                throw new Exception('Folders not found.');
            }

            $hiring_organization = HiringOrganization::find($role->entity_id);
            return $hiring_organization->folders->count();
        } catch (Exception $e) {
            return $this->handleError($e, 500);
        }
    }


    /**
     * @param  Request  $request
     * @param  Folder  $folder
     * @return bool
     */
    private function checkPermissions(Request $request, Folder $folder)
    {

        if ($request->user()->role->entity_id !== $folder->hiring_organization_id) {
            return response('Hiring Organization Not Authorized', 403);
        }

        $user_departments = $request->user()->role->departments->pluck('id')->toArray();

        $folder_department = $folder->departments()->first();

        if (!in_array($folder_department->id, $user_departments)) {
            return response('Department Not Authorized', 403);
        }

        return true;

    }

}
