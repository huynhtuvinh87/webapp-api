<?php

namespace Tests;

use App\Models\DynamicForm;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

abstract class TestCase extends BaseTestCase
{

    use CreatesApplication;

    public static $isDBInit = true;

    public static function setUpBeforeClass()
    {

        parent::setUpBeforeClass();

        $isDebug = env('APP_DEBUG', false);
        $isLocalWeb = env('WEB_APP_URL', "") == "http://localhost:8080/#";
        $isDevelopEnv = env('APP_ENV', "unknown") == "develop";

        if (!static::$isDBInit) {

            // if ($isDebug && $isLocalWeb && $isDevelopEnv) {
            static::setUpDB();
            // } else {
            //     // Thrown when APP_DEBUG is false
            //     if (!$isDebug) {
            //         throw new Exception("Not in a debug environment");
            //     }
            //     // Thrown when WEB_APP_URL is not localhost
            //     else if (!$isLocalWeb) {
            //         throw new Exception("Web url is " . $env('WEB_APP_URL', "") . "");
            //     }
            //     // Thrown when APP_ENV is not `develop`
            //     else if (!$isDevelopEnv) {
            //         throw new Exception("App Environment is not 'develop'.");
            //     } else {
            //         // NOTE: This should never be thrown, but just in case
            //         throw new Exception("Not a proper development environment!");
            //     }
            // }
        }
    }

    public function setUp(): void
    {
        parent::setUp();
    }

    public static function setUpDB()
    {
        print("\nSetting up");

        // Clearing caches
        print("\n\tClearing caches...");
        try {
            // shell_exec("composer dumpautoload > /dev/null 2>&1");
            // shell_exec("php artisan cache:clear > /dev/null 2>&1");
            print("complete");
        } catch (Exception $e) {
            print("failed");
        }

        // migrate:fresh
        print("\n\tClearing the DB...");
        try {
            shell_exec("php artisan migrate:fresh > /dev/null 2>&1");
            print("complete");
        } catch (Exception $e) {
            print("failed");
        }

        // Key gen
        print("\n\tGenerating Keys...");
        try {
            shell_exec("php artisan key:gen > /dev/null 2>&1");
            print("complete");
        } catch (Exception $e) {
            print("failed");
        }

        // Installing passport
        print("\n\tInstalling passport...");
        try {
            shell_exec("php artisan passport:install > /dev/null 2>&1");
            print("complete");
        } catch (Exception $e) {
            print("failed");
        }

        // db:seed
        print("\n\tSeeding defaults...");
        try {
            shell_exec("php artisan db:seed > /dev/null 2>&1");
            print("complete");
        } catch (Exception $e) {
            print("failed");
        }

        // db:seed
        print("\n\tSeeding test Hiring Organization...");
        try {
            shell_exec("php artisan db:seed --class=HiringOrganizationSeeder > /dev/null 2>&1");
            print("complete");
        } catch (Exception $e) {
            print("failed");
        }

        print("\ncomplete");
        print("\n\n");
        static::$isDBInit = true;
    }

    public static function isSetupNeeded()
    {
        try {
            DynamicForm::max('id')->get();
        } catch (Exception $e) {
            return true;
        }

        return false;
    }
}
