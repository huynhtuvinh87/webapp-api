<?php

namespace Notifications;

use App\Jobs\Notifications\SendInviteContractorOwner;
use App\Models\HiringOrganization;
use App\Models\User;
use App\Notifications\Registration\InviteContractorOwner;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class InviteContractorOwnerTest extends TestCase
{
    private $hiring_org_id = 144; // ALC Schools

    public function setUp(): void
    {
        parent::setUp();
        $this->token = Str::random(64);
        $this->user = factory(User::class)->create(['email_verified_at' => Carbon::now()]);
        $this->hiring_organization = HiringOrganization::find($this->hiring_org_id);
        $this->coupon = "TEST-ALC-COUPON-12345";

        $this->assertInstanceOf(User::class, $this->user);
        $this->assertInstanceOf(HiringOrganization::class, $this->hiring_organization);
    }

    /**
     * @group Notifications
     */
    public function testQueueEmail()
    {
        Queue::fake();
        SendInviteContractorOwner::dispatch($this->user, $this->hiring_organization, $this->token);
        Queue::assertPushed(SendInviteContractorOwner::class, function ($job) {
            return $job->queue;
        });
    }

    /**
     * @group Notifications
     */
    public function testSendEmail()
    {
        if (config('app.env') != 'production') {
            $user = $this->user;
            $user->notify(new InviteContractorOwner($this->hiring_organization, $this->coupon, $this->token));
            $this->assertEquals(1, 1);
        }
    }

    /**
     * @group Notifications
     */
    public function testAttachmentExist()
    {
        $this->assertFileExists(storage_path('How to Register - Contractor Compliance.pdf'));
    }

    /**
     * @group Notifications
     */
    public function testNotification()
    {
        try {
            $user = $this->user;

            Notification::fake();

            $user->notify(new InviteContractorOwner($this->hiring_organization, $this->coupon, $this->token));

            Notification::assertSentTo(
                $user,
                InviteContractorOwner::class,
                function ($notification) use ($user) {
                    $notifiable = $notification->toMail($user);
                    $this->assertEquals("Re: ALC Schools - Registration Required", $notifiable->subject);
                    $blade_file = "resources/views/" . str_replace(".", "/", $notifiable->view) . ".blade.php";
                    $this->assertFileExists($blade_file, "Blade file found it!");
                    $this->assertArrayHasKey('file', $notifiable->attachments[0]);
                    return true;
                }
            );
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    /**
     * @group Notifications
     */
    public function testCleanDatabase()
    {
        try {
            User::destroy($this->user->id);
            $this->assertDatabaseMissing('users', ['id' => $this->user->id]);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
}
