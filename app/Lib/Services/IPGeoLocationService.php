<?php

namespace App\Lib\Services;

use App\Models\IPGeoLocation;
use App\Traits\ExternalAPITrait;
use Exception;
use Log;

class IPGeoLocationService
{
    use ExternalAPITrait;

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

    /**
     * Gets location by IP Address
     *
     * @return void
     */
    public static function getRegionByIP($ipAddress)
    {
        $region = null;

        try {

            if (!isset($ipAddress)) {
                Log::warn("IP address is not defined, returning default region");
                return self::DEFAULT_REGION;
            }

            $region = self::dbRegion($ipAddress);

            // using ipapi.co location if not stored in DB
            if(!isset($region)){
                $region = self::ipapicoRegion($ipAddress);
            }

            // NOTE: Put subsequent 3rd parties here

            if (!isset($region)) {
                throw new Exception("Could not determine region by IP. All resources were exhausted.");
            }

            // Verifying returned region is in the list of regions
            // if (!self::isValidDisk($region)) {
            //     throw new Exception("Region $region is not valid region.");
            // }
        } catch (Exception $e) {
            // If error, return default region
            Log::warn($e->getMessage());
            Log::warn('Returning default region');
            try{
                self::updateIPGeoDBInfo($ipAddress, strtoupper(self::DEFAULT_REGION), 'default');
            } catch(Exception $e){
                // Do nothing
            }
            return self::DEFAULT_REGION;
        }

        return $region;
    }

    /**
     * Using ipapi.co to determine location
     *
     * @param [type] $ipAddress
     * @return void
     */
    public static function ipapicoRegion($ipAddress)
    {
        if (!isset($ipAddress)) {
            throw new Exception("Address was not defined");
        }

        $url = "https://ipapi.co/$ipAddress/json/";
        $result = self::externalRequest("GET", $url);
        $region = self::DEFAULT_REGION;
        /** Response value region is based on */
        $resultCC = $result->country_code ?? null;

        try {
            if (!isset($result->country_code)) {
                throw new Exception("ipapi.co could not determine country");
            }

            $region = self::countryCodeToRegion($resultCC);
            self::updateIPGeoDBInfo($ipAddress, $resultCC, 'ipapi.co');
        } catch (Exception $e) {
            Log::debug("ipapi.co Could not determine country code by region", [
                'exception' => $e->getMessage(),
                'url' => $url,
                'result' => $result,
            ]);
            return null;
        }

        return $region;
    }

    /**
     * Get region by using the DB
     *
     * @param [type] $ipAddress
     * @return void
     */
    public static function dbRegion($ipAddress)
    {
        $geo = IPGeoLocation::where('ip_address', $ipAddress)->first();
        if (!isset($geo)) {
            Log::debug("IP Address was not found in DB $ipAddress");
            return null;
        }

        $region = self::countryCodeToRegion($geo->country_code);

        return $region;
    }

    public static function updateIPGeoDBInfo($ipAddress, $countryCode, $source)
    {
        if (!isset($countryCode)) {
            throw new Exception("Country code was not defined");
        }

        if (!isset($ipAddress)) {
            throw new Exception("IP Address was not defined");
        }

        $ipGeo = IPGeoLocation::updateOrCreate([
            'ip_address' => $ipAddress,
        ], [
            'country_code' => $countryCode,
            'source' => $source,
        ]);
    }

    /**
     * Works only for the ISO 3166 standard
     *
     * @param [type] $countryCode
     * @return void
     */
    public static function countryCodeToRegion($countryCode)
    {
        $region = null;

        switch ($countryCode) {
            case 'US':
                $region = 'us';
                break;
            case 'CA':
                $region = 'ca';
                break;
            default:
                Log::debug("Could not determine region for '$countryCode'.");
        }

        return $region;
    }
}
