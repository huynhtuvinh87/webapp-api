<?php

namespace App\Traits;

use App\Models\RequestLog;
use Exception;
use Illuminate\Http\Request;
use Log;

trait ControllerTrait
{

    /**
     * Logs the request provided
     * Use this only when executing sensitive actions, or updating information
     * Don't care about readers
     *
     * Simply use the following line to log the information:
     * $this->logRequest($request, __METHOD__);
     *
     * @param Request $request
     * @param String $method
     * @return void
     */
    public function logRequest(Request $request, String $method)
    {
        try {
            $fieldsNotToStore = [
                'password',
                'password_confirmation',
                'new_password',
                'new_password_confirmation',
                'token'
            ];

            $requestLogData = [
                'ip_address' => $request->ip(),
                'route' => $request->fullUrl(),
                'method' => $method,
                'token' => $request->bearerToken(),
                'body' => json_encode($request->except($fieldsNotToStore)),
            ];

            try {
                $requestLogData['user'] = $request->user()->id;
            } catch (Exception $e) {
                Log::warn($e->getMessage());
            }

            RequestLog::create($requestLogData);

            Log::debug('request', $requestLogData);
        } catch (Exception $e) {
            Log::warn("Failed to create request log", [
                'message' => $e->getMessage(),
            ]);
        }
    }

}
