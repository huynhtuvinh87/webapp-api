<?php

namespace App\Traits;

use App\Models\File;
use App\Models\Role;
use Log;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Models\RequirementHistory;
use App\Models\FileRequirementHistory;


trait FileTrait
{
    /**
     * Creates a file in the DB and stores it on Amazon
     *
     * @param User $user
     * @param [type] $ipAddress
     * @param UploadedFile $uploadedFile
     * @return void
     */
    public function createFile(User $user, $ipAddress, UploadedFile $uploadedFile)
    {
        Log::debug(__METHOD__);
        $file = File::create([
            'path' => $uploadedFile->path(),
            'role_id' => $user->role->id,
            'ip' => $ipAddress,
        ]);
        $file->storeFile($uploadedFile, $ipAddress);

        return $file;
    }

    /**
     * Creates a file and uploads it to amazon based on the request, and which request prop is the file
     *
     * @param Request $request
     * @param [type] $fileFiled
     * @return void
     */
    public function createFileFromRequest(Request $request, $fileFiled)
    {
        $user = $request->user();
        $ipAddress = $request->ip();
        $hasFile = $request->hasFile($fileFiled);

        // Error handling
        if (!$hasFile) {
            throw new Exception("No file was provided to be uploaded!");
        }

        if(!isset($user)){
            throw new Exception("User was not defined. Please log in, and try again.");
        }

        $uploadedFile = $request->file($fileFiled);     
        if(is_array($uploadedFile)){
        $num_files = count($uploadedFile);
        $files = [];
            for ($i = 0; $i < $num_files; $i++) {
                $file = $this->createFile($user, $ipAddress, $uploadedFile[$i]);
                $files[$i] = $file;
            }
            return $files;
        } else {
            $file = $this->createFile($user, $ipAddress, $uploadedFile);
            return $file;
        }

    }
    /**
     * Takes one or many files and creates records in requirement_histories and requirement_history_files
     * Properties in argument object:
     * requirement_id, completion_date, role_id, contractor_id, file or array of files
     */
    public function createRequirementHistoryFile($reqHistoryFileObj){
        $reqHistory = RequirementHistory::create([
            "requirement_id" => $reqHistoryFileObj->requirement_id,
            "completion_date" => $reqHistoryFileObj->completion_date,
            "role_id" => $reqHistoryFileObj->role_id,
            "contractor_id" => $reqHistoryFileObj->contractor_id,
            "resource_id" => isset($reqHistoryFileObj->resource_id) ? $reqHistoryFileObj->resource_id : null
        ]);
        // Iterate the array if more than one file uploaded
        // Else use the file returned to create a requirement history file
        $file = $reqHistoryFileObj->file;
        if (is_array($file)){
            $num_files = count($file); 
            for ($i = 0; $i < $num_files; $i++) {
                $reqHistory->files()->attach($file[$i]);
            }
        }
        else {
            $reqHistory->files()->attach($file);
        }

        return $reqHistory;
    }

    public function readFileResponse(Request $request, File $file)
    {
        if (!isset($file)) {
            throw new Exception("No file");
        }

        $user = $request->user();
        $role = $user->role;

        if (!$file->canReadFile($role)) {
            throw new Exception("Invalid permissions to access this file.");
        }

        // Cleaning up file object
        if (isset($file->role) && isset($file->role->company)) {
            unset($file->role->company);
        }

        return $file;
    }

}
