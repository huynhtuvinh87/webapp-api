<?php

namespace Notifications;

use App\Models\User;
use App\Notifications\Registration\Welcome;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Log;
use Tests\TestCase;

class WelcomeEmailNotificationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create(['email_verified_at' => Carbon::now()]);
        $this->assertInstanceOf(User::class, $this->user);

    }

    /**
     * @group Notifications
     */
    public function testNotification()
    {
        $user = $this->user;

        if (config('app.env') != 'production'){
            $this->user->notify(new Welcome());
            $this->assertEquals(true, true);
        }

        Notification::fake();

        $user->notify(new Welcome());

        Notification::assertSentTo(
            $user,
            Welcome::class,
            function ($notification) use ($user){
                $notifiable = $notification->toMail($user);
//                Log::info(json_encode($notifiable));
                $this->assertEquals("Re: Welcome to Contractor Compliance!", $notifiable->subject);
                $blade_file = "resources/views/" . str_replace(".", "/", $notifiable->view) . ".blade.php";
                $this->assertFileExists($blade_file,"Blade file found it!");
                return true;
            }
        );
    }

    /**
     * @group Notifications
     */
    public function testDeleteUser()
    {
        try{
            User::destroy($this->user->id);
            $this->assertDatabaseMissing('users', ['id'=>$this->user->id]);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

}
