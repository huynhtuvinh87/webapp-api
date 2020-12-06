<?php

namespace App\Models;

use App\Lib\Services\IPGeoLocationService;
use App\Models\Role;
use App\Models\RequirementHistory;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Log;

/**
 * Model to store and handle files uploaded to the system
 */
class File extends Model
{
    use SoftDeletes;

    protected $appends = ['fullPath', 'size'];

    /** Mapping of regions to disks */
    const REGION_DISKS = [
        'us' => 's3_us',
        'ca' => 's3_ca',
        'public' => 'public',
    ];

    /**
     * Default region: Canada
     *
     * Spoke to mark - people are scared of the US patriot act where gov't can just go in and seize all your stuff.
     */
    const DEFAULT_REGION = 'ca';

    public $fillable = [
        'name',
        'path',
        'ext',
        'role_id',
        'ip',
        'disk',
        'visibility',
        'storage_size',
        'updated_at',
        'created_at',
        'deleted_at',
    ];

    private const DOC_TYPE_IMAGE = [
        "jpg",
        "jpeg",
        "png",
        "gif",
        "webp",
        "svg",
        "bmp",
        "webp",
        "bat",
        "ppm",
        "pgm",
        "pbm",
        "pnm",
    ];
    private const DOC_TYPE_OFFICE = [
        "doc",
        "dot",
        "wbk",
        "docx",
        "docm",
        "dotx",
        "dotm",
        "docb",
        "xls",
        "xlsx",
        "xlt",
        "xlm",
        "xlsm",
        "xltx",
        "xltm",
        "xlsb",
        "xla",
        "xlam",
        "xll",
        "xlw",
        "ppt",
        "pot",
        "pps",
        "pptx",
        "pptm",
        "potx",
        "potm",
        "ppam",
        "ppsx",
        "ppsm",
        "sldx",
        "sldm",
        "ACCDB",
        "ACCDE",
        "ACCDT",
        "ACCDR",
        "pub",
        "xps",
        // Legacy issue for docx and xlsx
        "ocx",
        "slx",
    ];

    private const OFFICE_FILE_PREVIEW_PREFIX = 'https://view.officeapps.live.com/op/view.aspx?src=';

    // Relations

    public function folders() : BelongsToMany
    {
        return $this->belongsToMany(Folder::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function requirementHistories() : BelongsToMany
    {
         return $this->belongsToMany(RequirementHistory::class);
    } 

   

    // Actions

    public function storeFile(UploadedFile $file, $ipAddress = null, $visibility = 'private')
    {
        if (!isset($this->role_id)) {
            throw new Exception("Role ID was not found!");
        }

        $disk = $this->getDiskByIP($ipAddress);
        $newPath = $this->getStoragePath();

        try {
            // Uploading file to AWS
            $name = $file
                ->store(
                    $newPath,
                    $disk
                );

            // Updating path and disk
            $this->update([
                'path' => $name,
                'disk' => $disk,
                'visibility' => $visibility,
                'ext' => $file->extension(),
                'name' => $file->getClientOriginalName(),
            ]);

            // Making sure file has been loaded
            $exists = Storage::disk($disk)->exists($this->path);
            if (!$exists) {
                throw new Exception("File was not stored properly. Please try again.");
            }
        } catch (Exception $e) {
            throw new Exception("Could not upload file: " . $e->getMessage());
        }
    }

    /**
     * Generates storage path
     * NOTE: Tried adding '/' to the end to make things more convenient,
     * but it adds an extra / somewhere else when reading files
     *
     * @return void
     */
    public function getStoragePath()
    {
        $appName = config('app.env', null);
        if (!isset($appName)) {
            throw new Exception("APP_ENV was not defined when determining storage path.");
        }
        $pathProps = [
            $appName,
            'files',
            isset($this->role_id) ? $this->role_id : null,
        ];
        $path = implode('/', $pathProps);
        return $path;
    }

    public function getFullPath($liveMinutes = null)
    {
        if (!isset($this->path)) {
            throw new Exception("Path is not defined - can't access file $this->id");
        }
        // Getting disk for file
        $disk = $this->disk;
        // Checking if file exists at that disk
        $exists = $this->doesFileExist();

        // If file does not exist, return false
        if (!$exists) {
            Log::warn("File $this->id was not found at '$this->path'");
            return false;
        }

        if ($this->disk != 'public') {
            $url = Storage::disk($disk)
                ->temporaryUrl(
                    $this->path,
                    now()->addMinutes($liveMinutes ?? 5)
                );
        } else {
            $url = Storage::disk($disk)->url($this->path);
        }

        return $url;
    }

    public function getFullPathAttribute()
    {
        $path = $this->getFullPath();

        if($this->isOfficeFile()){
            $path = $this::OFFICE_FILE_PREVIEW_PREFIX . rawurlencode($path);
        }

        return $path;
    }

    public function doesFileExist()
    {
        // If marked as deleted, return false
        if ($this->deleted_at != null) {
            return false;
        }

        $fileExists = Storage::disk($this->disk)->exists($this->path);

        // If file does not exist anymore, mark it as deleted
        if (!$fileExists) {
            $this->delete();
        }

        return $fileExists;
    }

    /**
     * Get the disk based on the IP address
     *
     * @param String $ipAddress
     * @return String
     */
    public function getDiskByIP($ipAddress = null)
    {
        $selectedRegion = null;
        $selectedDisk = $this::REGION_DISKS[$this::DEFAULT_REGION];

        if (!isset($ipAddress)) {
            $ipAddress = $this->ip;
        }

        //Get region by IP
        if (isset($ipAddress)) {
            try {
                $selectedRegion = IPGeoLocationService::getRegionByIP($ipAddress);
            } catch (Exception $e) {
                // Do nothing if failed to get selected region
                // Disk defaults to default region
            }
        }

        // Determine disk by region
        if (isset($this::REGION_DISKS[$selectedRegion])) {
            $selectedDisk = $this::REGION_DISKS[$selectedRegion];
        }

        return $selectedDisk;
    }

    /**
     * Get disk by user's profile
     *
     * @param User $user
     * @return String
     */
    public function getDiskByUser(User $user)
    {
        throw new Exception("INCOMPLETE");
    }

    /**
     * Checks to see if the user can read the file
     * Cases a user can see a private file:
     *      Connected through position
     *      Contractor / Hiring org is connected
     *
     * @param Role $requester
     * @param File $file
     * @return boolean
     */
    public function canReadFile(Role $requester)
    {
        $isSameCompany = $this->visIsSameCompany($requester);
        $companiesConnected = $this->visCompaniesConnected($requester);

        // If file is associated with a requirement history
        $reqHistories = $this->requirementHistories;
        $internalDocReqs = $reqHistories
            ->map(function($history){
                return $history->requirement;
            })
            // If requirement type is an internal_document
            ->filter(function($requirement){
                return $requirement->type == 'internal_document';
            });

        // Hide if the requirement is an internal doc, and not from the same company
        if(sizeof($internalDocReqs) > 0 && !$isSameCompany){
            return false;
        }

        // If visibility is public, then show file;
        if ($this->visibility == 'public') {
            return true;
        }

        // Requester has a role at the same company that the file was uploaded at
        if ($isSameCompany) {
            return $isSameCompany;
        }

        // Check if the companies are connected at all
        if ($companiesConnected) {
            return $companiesConnected;
        }

        // If nothing else passed, return false
        return false;
    }

    /**
     * Returns true / false if the companies are connected
     * Companies can be HiringOrganization or Contractor
     *
     * @param mixed $companyA
     * @param mixed $companyB
     * @return void
     */
    public function visCompaniesConnected($requester)
    {
        $requesterCompany = $requester->company;

        $role = $this->role()
            ->withTrashed()
            ->first();

        if(!isset($role)){
            // throw new Exception("Role could not be found for the current file - " . $this->id . ". Role ID: " . $this->role_id);
            return false;
        }

        $fileCompany = $role->company;

        $requesterCompanyConnections = null;

        if (get_class($requesterCompany) == HiringOrganization::class) {
            $requesterCompanyConnections = $requesterCompany->contractors;
        } else if (get_Class($requesterCompany) == Contractor::class) {
            $requesterCompanyConnections = $requesterCompany->hiringOrganizations;
        }

        if (!isset($requesterCompanyConnections)) {
            Log::warn("Could not find any connections for requester company");
            return false;
        }

        /** Count of connections between contractor and hiring org - should be only 1 */
        $matchedConnections = $requesterCompanyConnections
            ->filter(function ($AToBCompany) use ($fileCompany) {
                return $AToBCompany->id == $fileCompany->id;
            });

        $connectionMatchCount = sizeof($matchedConnections);

        // If the two companies have more than one connection, log it
        if ($connectionMatchCount > 1) {
            Log::warn("Hiring Org / Contractor has more than 1 connection", [
                'Requester Company id' => $requesterCompany->id,
                'Requester Company Type' => get_class($requesterCompany),
                'File Company id' => $fileCompany->id,
                'File Company Type' => get_class($fileCompany),
            ]);
        }

        return $connectionMatchCount > 0;
    }

    /**
     * Returns true / false if the requester is a part of the same company as the file
     *
     * @param Role $requester
     * @return void
     */
    public function visIsSameCompany(Role $requester)
    {

        /** Company the current file is associated with */
        $role = $this->role()
            ->withTrashed()
            ->first();

        if(!isset($role)){
            // throw new Exception("Role could not be found for the current file - " . $this->id . ". Role ID: " . $this->role_id);
            return false;
        }
        $fileCompany = $role->company;
        if(!isset($fileCompany)){
            throw new Exception("Company for file through role, not found");
        }
        $isSameCompany = $requester->company->id == $fileCompany->id;
        return $isSameCompany;
    }

    /**
     * Move the file to the specified location
     *
     * @param String $args Contains information on how to move the file.
     * String $args->path: Folder to put the file - dont end with '/'
     * @param Boolean $deleteOriginal Delete the original file after moving
     *
     *
     */
    public function move($args, $deleteOriginal = false)
    {
        // TODO: Maybe try the following instead:
        // Storage::disk('FTP')->writeStream('new/file1.jpg', Storage::readStream('old/file1.jpg'));

        $oldPath = $this->path;
        $oldDisk = $this->disk;
        $newBasePath = $args['path'] ?? null;
        $newDisk = $args['disk'] ?? null;
        $newName = $args['name'] ?? null;

        // Verify file exists
        if (!$this->doesFileExist()) {
            throw new Exception("Cannot move file " . $this->path . " - File does not exist");
        }

        // If new disk is not defined, use the current one
        if (!isset($newDisk)) {
            $newDisk = $this->getDiskByIP();
        }

        // If new path is not defined, use the generic one
        if (!isset($newBasePath)) {
            $newBasePath = $this->getStoragePath();
        }
        if(!isset($newName)){
            $newName = $this->getHashedFileName();
        }

        $newPath = $newBasePath . '/' . $newName;

        // Updating file information
        try {
            // Move files
            $currentFile = Storage::disk($this->disk)->get($this->path);
            Storage::disk($newDisk)->put($newPath, $currentFile);

            $this->update([
                'disk' => $newDisk,
                'path' => $newPath,
            ]);

            if (!$this->doesFileExist()) {
                throw new Exception("Error uploading file to server!!");
            }

            if ($deleteOriginal) {
                Storage::disk($oldDisk)->delete($oldPath);
            }

        } catch (Exception $e) {
            Log::error("Failed to move file", [
                'disk' => $this->disk,
                'path' => $this->path,
                'new disk' => $newDisk,
                'new path' => $newPath,
            ]);
            throw $e;
        }
    }

    public function updateFromPath()
    {
        $path_parts = pathinfo($this->path);
        if (isset($path_parts)) {
            $this->update([
                'name' => $path_parts['basename'] ?? '',
                'ext' => $path_parts['extension'] ?? '',
            ]);
        }
    }

    /**
     * Generates and returns a hashed file name
     *
     * @return String {hash}.{extension}
     */
    public function getHashedFileName()
    {
        $hash = sha1(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));
        $fileName = $hash . '.' . $this->ext;

        return $fileName;
    }

    public function isValidDisk($disk)
    {
        // Checking if param is set
        if (!isset($disk)) {
            throw new Exception("Disk was not defined");
        }

        // Checking if constant is set
        if ($this::REGION_DISKS == null) {
            throw new Exception("Region Disks were not defined");
        }

        // Checking if valid disk
        return isset($this::REGION_DISKS[$disk]);
    }

    public function size()
    {
        $size = 0;
        try {
            if (isset($this->storage_size) && $this->storage_size != null && $this->storage_size != '') {
                $size = $this->storage_size;
            } else {
                $size = Storage::disk($this->disk)->size($this->path);
                $this->update([
                'storage_size' => $size,
            ]);
            }
        } catch (Exception $e){
            Log::error($e->getMessage());
        } finally {
            return $size;
        }
    }

    public function getSizeAttribute(){
        return $this->size();
    }

    private function isOfficeFile(){
        return in_array($this->ext, $this::DOC_TYPE_OFFICE);
    }

    private function isImageFile(){
        return in_array($this->ext, $this::DOC_TYPE_IMAGE);
    }

}
