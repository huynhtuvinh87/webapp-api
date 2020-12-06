<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ErrorHandlingTrait;
use App\Traits\TranslationTrait;
use Exception;
use Illuminate\Http\Request;
use Log;

class TranslationController extends Controller
{
    use ErrorHandlingTrait;
    use TranslationTrait;

    /**
     * Translates using the following parameters:
     * source_text
     * target_lang
     *
     * @param Request $request
     * @return void
     */
    public function read(Request $request)
    {
        Log::debug(__METHOD__);
        try {
            if ($request->has('translations')) {
                return $this->bulkRead($request);
            } else {
                return $this->indvRead($request);
            }
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Returns all translated text for a given language
     */
    public function readAll(Request $request, String $targetLang)
    {
        try {
            if (!$targetLang) {
                throw new Exception("Can't find target language");
            }
            // $targetLang = $request->has('lang');
            return $this->getAllTranslations($targetLang);

        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Individual read request
     *
     * @param Request $request
     * @return void
     */
    public function indvRead(Request $request)
    {
        Log::debug(__METHOD__);
        try {

            // Validating Request
            $this->validate($request, [
                'source_text' => 'required',
                'target_lang' => 'required',
            ]);

            $t = $this->translateString(
                $request->get('source_text'),
                $request->get('target_lang')
            );

            return response($t);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Translates using an array of source_text / target_lang pairing
     * should be in $request->get('translations')
     *
     * @param Request $request
     * @return void
     */
    public function bulkRead(Request $request)
    {
        Log::debug(__METHOD__);
        try {
            // Validating Request
            $this->validate($request, [
                'translations' => 'required',
                'target_lang' => 'required',
            ]);

            $translations = $this->translateBulk(
                $request->get('translations'),
                $request->get('target_lang')
            );

            $translations = $translations
                ->flatMap(function ($translation) use ($request) {
                    return [
                        $translation->source_text => [
                            // 'source_text' => $translation->source_text,
                            'target_text' => $translation->target_text,
                        ],
                    ];
                })
                ->all();

            return $translations;
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

}
