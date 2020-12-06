<?php

namespace App\Traits;

use Exception;
use Log;

trait ErrorHandlingTrait
{
    public function errorResponse(Exception $exception, $errorCode = 500)
    {
        Log::error($exception->getMessage());
        return response([
            'message' => $exception->getMessage(),
        ], $errorCode);
    }
}
