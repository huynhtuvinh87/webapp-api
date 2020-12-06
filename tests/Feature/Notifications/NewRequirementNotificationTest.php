<?php

namespace Notifications;

use App\Jobs\Notifications\SendNewRequirementNotification;
use App\Models\HiringOrganization;
use App\Models\Position;
use App\Models\User;
use App\Notifications\Requirement\NewRequirement;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NewRequirementNotificationTest extends TestCase
{
    private $position;
    private $hiring_organization;
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create(['email_verified_at' => Carbon::now()]);
        $this->hiring_organization = factory(HiringOrganization::class)->create();
        $this->position = factory(Position::class)->create(['position_type' => 'contractor', 'hiring_organization_id' => $this->hiring_organization->id]);

        $this->assertInstanceOf(User::class, $this->user);
        $this->assertInstanceOf(HiringOrganization::class, $this->hiring_organization);
        $this->assertInstanceOf(Position::class, $this->position);
    }

    /**
     * @group Notifications
     */
    public function testQueueEmail()
    {
        Queue::fake();
        SendNewRequirementNotification::dispatch($this->user, $this->position);
        Queue::assertPushed(SendNewRequirementNotification::class, function ($job) {
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
            $user->notify(new NewRequirement($this->hiring_organization->name, false));
            $this->assertEquals(1, 1);
        }
    }

    /**
     * @group Notifications
     */
    public function testNotification()
    {
        try {
            $user = $this->user;

            Notification::fake();

            $user->notify(new NewRequirement($this->hiring_organization->name, false));

            Notification::assertSentTo(
                $user,
                NewRequirement::class,
                function ($notification) use ($user) {
                    $notifiable = $notification->toMail($user);
                    $this->assertEquals("Re: " . ucwords($this->hiring_organization->name) . " Has Assigned New Requirements to " . ucwords($user->first_name), $notifiable->subject);
                    $blade_file = "resources/views/" . str_replace(".", "/", $notifiable->view) . ".blade.php";
                    $this->assertFileExists($blade_file, "Blade file found it!");
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

            HiringOrganization::destroy($this->hiring_organization->id);
            $this->assertDatabaseMissing('hiring_organizations', ['id' => $this->hiring_organization->id]);

            Position::destroy($this->position->id);
            $this->assertDatabaseMissing('positions', ['id' => $this->position->id]);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
}
