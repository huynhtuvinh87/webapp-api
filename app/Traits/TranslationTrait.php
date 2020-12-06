<?php

namespace App\Traits;

use App\Models\Translation;
use App\Traits\ExternalAPITrait;
use Exception;
use Google\Cloud\Translate\V2\TranslateClient;
use Log;

trait TranslationTrait
{
    use ExternalAPITrait;

    public function translateString(String $source_text, String $target_lang, String $source_lang = 'en')
    {
        // Check if translation already exists
        $translationQuery = Translation::where('source_text', $source_text)
            ->where('target_lang', $target_lang);

        $existingTranslation = $translationQuery->first();

        $translationExists = isset($existingTranslation);

        // If translation already exists, return
        if ($translationExists) {
            return $existingTranslation;
        }

        $googleTranslated = $this->googleIndivTranslate($source_text, $target_lang, $source_lang);

        return $googleTranslated;
    }

    public function translateBulk($texts, String $target_lang, String $source_lang = 'en')
    {
        try {

            Log::debug(__METHOD__);
            Log::debug("Bulk Translation Props", [
                'texts' => $texts,
                'target_lang' => $target_lang,
                'source_lang' => $source_lang,
            ]);

            $textsToTranslate = collect($texts)
                ->map(function ($text) use ($target_lang) {
                    // Check if text is already translated
                    $existingTranslation = Translation::where('source_text', $text)
                        ->where('target_lang', $target_lang)
                        ->first();

                    // If it already exists, return
                    if (isset($existingTranslation)) {
                        // return $existingTranslation;
                        return null;
                    }

                    // if text doesn't exist, add it to bulk translation array
                    return $text;
                })
                ->filter()
                ->values()
                ->all();

            // translating missing text
            $countTextsToTranslate = sizeof($textsToTranslate);
            if ($countTextsToTranslate > 0) {
                Log::debug("There are $countTextsToTranslate texts to translate");
                $this->googleBulkTranslate($textsToTranslate, $target_lang);
            }

            // Retrieve all texts to translate
            $translations = Translation::whereIn('source_text', $texts)
                ->where('target_lang', $target_lang)
                ->get();

            return $translations;
        } catch (Exception $e) {
            Log::error(__METHOD__, [
                'message' => "Failed to translate",
                'error' => $e,
            ]);
            $translations = Translation::where('target_lang', $target_lang)
                ->get();
        }

    }

    public function googleBulkTranslate($texts, String $target_lang, String $source_lang = 'en')
    {
        Log::info(__METHOD__);
        Log::debug("googleBulkTranslate Props", [
            'texts' => $texts,
            'target_lang' => $target_lang,
            'source_lang' => $source_lang
        ]);
        // https://cloud.google.com/translate/docs/simple-translate-call

        $apiKey = config('app.google.translation_key', null);
        $googleProjectName = 'contractorcomplianceapp';

        try {
            $tConfig = [
                'key' => $apiKey,
                'format' => 'text',
                'target' => $target_lang,
            ];

            // Sends translation to google
            $tClient = new TranslateClient($tConfig);
            $response = $tClient->translateBatch($texts);

            // Stores translation results in DB
            $translations = collect($response)
                ->map(function ($translation) use ($target_lang) {
                    // Creating and returning translation model object
                    return $this->storeTranslation(
                        $translation['input'],
                        $target_lang,
                        $translation['text'],
                        'google',
                        $translation['source'],
                    );
                });

            return $translations;

        } catch (Exception $e) {
            Log::error("Failed to get translation from google", [
                'message' => $e->getMessage(),
                'token' => $apiKey,
            ]);
            throw $e;
        }
    }

    public function googleIndivTranslate(String $source_text, String $target_lang, String $source_lang = 'en')
    {
        Log::debug(__METHOD__);
        // https://cloud.google.com/translate/docs/simple-translate-call

        $apiKey = config('app.google.translation_key', null);
        $googleProjectName = 'contractorcomplianceapp';

        try {

            $tConfig = [
                'key' => $apiKey,
                'format' => 'text',
                'target' => $target_lang,
            ];
            $tClient = new TranslateClient($tConfig);
            $response = $tClient->translate([$source_text]);
            $translationText = $response['text'];

            Log::info("Translation", [
                'response' => $response,
            ]);

            $translation = $this->storeTranslation($source_text, $target_lang, $translationText, 'google', $source_lang);

            return $translation;

        } catch (Exception $e) {
            Log::debug("Failed to get translation from google", [
                'message' => $e->getMessage(),
                'token' => $apiKey,
            ]);
            throw $e;
        }

    }

    private function storeTranslation(String $source_text, String $target_lang, String $target_text, String $reference, String $source_lang = 'en')
    {
        $t = Translation::updateOrCreate([
            'source_text' => $source_text,
            'source_lang' => $source_lang,
            'target_text' => $target_text,
            'target_lang' => $target_lang,
            'reference' => $reference,
            'environment' => config('app.env', 'development'),
        ]);

        return $t;
    }

    private function getAllTranslations(String $target_lang){
        return Translation::where('target_lang', $target_lang)
            ->get()
            ->flatMap(function($language){
                return [
                    $language->source_text => [
                        'target_text' => $language->target_text
                    ]
                ];
            });
    }
}
