<?php

namespace App\Traits;

use Exception;
use Log;
use GuzzleHttp\Client;

trait ExternalAPITrait
{

    public static function externalRequest($requestType, $url, $params = [])
    {
        // http://docs.guzzlephp.org/en/stable/quickstart.html

        $client = new Client();
        $response = $client->request($requestType, $url, $params);
        $statusCode = $response->getStatusCode();

        if ($statusCode != 200) {
            throw new Exception("Status code was " . $statusCode);
        }

        $body = $response->getBody();
        $stringBody = (string) $body;


        $isJson = false;

        // Determining if response is JSON
        if($response->hasHeader('Content-Type')){
            $contentType = $response->getHeader('Content-Type')[0];
            $isJson = $contentType == 'application/json';
        }

        if($isJson){
            return json_decode($stringBody);
        } else {
            return $stringBody;
        }

    }

}
