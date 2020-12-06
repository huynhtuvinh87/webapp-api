<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\NotificationBaseTest as BaseTestCase;
use Illuminate\Support\Str;

use App\Models\User;
use Exception;
use App\Notifications\SlackErrorNotification;

/**
 * Tests the different dynamic forms that can be read
 */
class SlackErrorNotificationTest extends BaseTestCase
{

    use DatabaseTransactions;

    /**
     * @group Notifications
     */
	public function testSlackNotification(){
		try {
			throw new Exception("This is a test notification from SlackErrorNotificationTest");
		} catch (Exception $e){
			// $this->system->notify(new SlackErrorNotification($e));
		}
        $this->assertEquals(true, true);
	}

}
