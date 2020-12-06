<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

use Exception;
use App\Models\User;

abstract class NotificationBaseTest extends BaseTestCase
{
	use CreatesApplication;

	protected $user;

    public static function setUpBeforeClass()
    {
		parent::setUpBeforeClass();
    }

    public function setUp() : void
    {
		parent::setUp();

		$this->user = User::where('email','mbania@contractorcompliance.io')
			->first();

		if(is_null($this->user)){
			throw new Exception("Could not find a user");
		}
    }

    public function checkResponse($response)
    {
        if ($response->status() >= 400) {
            $message = json_decode($response->content())->message;
            if($message != ""){
                throw new Exception("\nResponse Message: \"" . $message . "\"");
            }

            $message = json_decode($response->content())->exception;
            if($message != ""){
                throw new Exception("\nResponse Exception: \"" . $message . "\"");
            }

            $message = $response->content();
            if($message != ""){
                throw new Exception("\nResponse Content: \"" . $message . "\"");
            }

            throw new Exception("Unknown Error");
        }
    }

}
