<?php

namespace App\Traits;

use Exception;

trait RequestTrait
{
    public function handleError(Exception $err, $code = null){
        if(!isset($code)){
            $code = 500;
        }

        return response([
            'message' => $err->getMessage()
        ], $code);
    }
}
