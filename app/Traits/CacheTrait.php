<?php

namespace App\Traits;

use App\Models\Contractor;
use App\Models\HiringOrganization;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Log;

/**
 * See webapp-api/app/Http/Middleware/ResponseCacheMiddleware.php for route caching implementation
 */
trait CacheTrait
{
    /**
     * Takes in a role and removes caches tagged with role
     *
     * @param [type] $role
     * @return void
     */
    public function clearCacheForRole(Role $role)
    {
        $roleTag = $this->getRoleCacheTag($role);
        Cache::tags($roleTag)->flush();
    }

    /**
     * Method to get cache tag
     */
    public function getRoleCacheTag(Role $role)
    {
        if (!isset($role)) {
            Log::debug(__METHOD__, [
            ]);
            throw new Exception("Role is not defined, cant determine cache tag");
        }
        return $this->buildTagFromObject($role);
    }

    /**
     * Method to get cache tag
     */
    public function getUserCacheTag(User $user)
    {
        if (!isset($user)) {
            Log::debug(__METHOD__, [
            ]);
            throw new Exception("User is not defined, cant determine cache tag");
        }
        return $this->buildTagFromObject($user);
    }

    /**
     * Method to get cache tag
     */
    public function getCompanyCacheTag(Role $role)
    {
        if (!isset($role)) {
            Log::debug(__METHOD__, [
            ]);
            throw new Exception("Role is not defined, cant determine cache tag");
        }

        $company = $role->company;
        if (!isset($company)) {
            Log::debug(__METHOD__, [
                'role' => $role,
            ]);
            throw new Exception("Company is null, cant determine cache tag");
        }

        if ($company instanceof HiringOrganization) {
            return $this->getHiringOrgCacheTag($company);
        } else if ($company instanceof Contractor) {
            return $this->getContractorCacheTag($company);
        } else {
            Log::debug("ERROR", [
                'role' => $role,
                'company' => $company,
            ]);
            throw new Exception("Could not determine company type");
        }
    }

    /**
     * Method to get cache tag
     */
    public function getHiringOrgCacheTag(HiringOrganization $company)
    {
        if (!isset($company)) {
            Log::debug(__METHOD__, [
            ]);
            throw new Exception("Company is null, cant determine cache tag");
        }

        return $this->buildTagFromObject($company);
    }

    /**
     * Method to get cache tag
     */
    public function getContractorCacheTag(Contractor $company)
    {
        if (!isset($company)) {
            Log::debug(__METHOD__, [
            ]);
            throw new Exception("Company is null, cant determine cache tag");
        }

        return $this->buildTagFromObject($company);
    }

    /**
     * Builds an array of cache tags based on the request
     */
    public function buildTagsFromRequest(Request $request, array $additionalTags = null)
    {
        $userKey = null;
        $roleKey = null;
        $companyKey = null;

        // User
        $user = $request->user();
        if ($user != null) {
            $userKey = $this->getUserCacheTag($user);

            // Role
            $role = $user->role;
            if ($role != null) {
                $roleKey = $this->getRoleCacheTag($role);
                $companyKey = $this->getCompanyCacheTag($role);
            }
        }

        $computedKeys = [$userKey, $roleKey, $companyKey];

        // Company Key

        // Tags from request params
        $requestProps = $request->all();

        foreach($requestProps as $key => $value){
            if(isset($key) && isset($value)&& !is_null($value) && $value){
                //TODO: FIX THIS? -- Need to check for array of files instead of a single one
                if ($key == 'attachment') {
                    continue;
                }
                $computedKeys = array_merge($computedKeys, [$this->buildTag($key, $value)]);
            }
        }

        // Merge additionalTags if its set
        if ($additionalTags != null && sizeof($additionalTags) > 0) {
            $keys = array_merge($computedKeys, $additionalTags);
        } else {
            $keys = $computedKeys;
        }

        return $keys;

    }

    /**
     * Takes a request and builds a cache key
     *
     * @param [type] $request
     * @return void
     */
    protected function buildKeyFromRequest($request)
    {

        $user = $request->user();
        $role = null;

        $userTag = null;
        $roleTag = null;

        $keyProps = [
            'request',
            $request->url(),
        ];

        if ($user != null) {
            $userTag = $this->getUserCacheTag($user);
            $keyProps[] = $userTag;

            $role = $user->role;
            if(isset($role)){
                $roleTag = $this->getRoleCacheTag($role);
                $keyProps[] = $roleTag;
            }
        }

        $requestProps = $request->all();

        foreach($requestProps as $key => $value){
            if(isset($value)){
                $newTag = $this->buildTag($key, $value);
                $keyProps[] = $newTag;
            }
        }

        /** Cache Key */
        $key = join('|', $keyProps);

        return $key;
    }

    /**
     * Takes in an object and builds a tag from it
     * "Key:Value"
     * If no keyName is provided, it finds the object class and uses that as the key
     *
     * @param [type] $object
     * @param [type] $keyName
     * @return void
     */
    protected function buildTag($key, $value)
    {
        if ($key == null || $value == null) {
            Log::warn(__METHOD__, [
                'key' => $key,
                'value' => $value,
            ]);
            throw new Exception("Missing Key / value");
        }

        if(is_array($key) || is_array($value)){
            Log::warn("Array passed into cache key builder");
            return null;
        }

        return strtoupper($key) . ":" . $value;
    }

    protected function buildTagFromObject($object)
    {
        if ($object == null) {
            throw new Exception("Object was null");
        }
        if(!is_object($object)){
            throw new Exception("Param was not an object");
        }
        if ($object->id == null) {
            throw new Exception("Object ID was not defined");
        }

        // If key name is not defined, get it from the class of the object
        $keyName = $this->getClassName($object);
        if ($keyName == '' || $keyName == null) {
            throw new Exception("Class name was not defined properly");
        }

        $tag = $this->buildTag($keyName, $object->id);

        return $tag;
    }

    /**
     * Takes in an object, and finds the final class name
     * If name is "App\Models\User", returns "User"
     *
     * @param [type] $object
     * @return void
     */
    protected function getClassName($object)
    {
        $className = get_class($object);
        $slashIndex = strrpos($className, '\\');
        if ($slashIndex != 0) {
            $name = substr($className, $slashIndex + 1);
        }
        return $name;
    }
}
