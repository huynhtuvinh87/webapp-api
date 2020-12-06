<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Traits\FileTrait;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Http\Request;
use Log;

class FileController extends Controller
{
    use FileTrait;
    use RequestTrait;

    /**
     * Create
     */
    public function create(Request $request)
    {
        Log::info(__METHOD__, [
            'user' => $request->user(),
            'request' => $request,
        ]);
        try {
            $file = $this->createFileFromRequest($request, 'file');
            return $this->read($request, $file);
        } catch (Exception $e) {
            return $this->handleError($e, 400);
        }
    }

    /**
     * Read files
     * @return {
     *      'file' => File
     *      'path' => String
     * }
     */
    public function read(Request $request, File $file)
    {

        try {
            $file = $this->readFileResponse($request, $file);

            // If file does not exist, throw 404 instead
            if (!$file->doesFileExist()) {
                $file->delete();
                return $this->handleError(new Exception("File not found"), 404);
            }

            return response([
                'file' => $file,
                'path' => $file->getFullPath(),
            ]);
        } catch (Exception $e) {
            Log::error(__METHOD__,[
                'error' => $e
            ]);
            return $this->handleError($e, 400);
        }
    }
}
