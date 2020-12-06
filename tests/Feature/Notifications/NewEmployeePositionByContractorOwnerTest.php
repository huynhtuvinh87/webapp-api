<?php

namespace Tests\Feature\Notifications;

use App\Jobs\Notifications\SendEmployeeNewPosition;
use App\Models\Contractor;
use App\Models\HiringOrganization;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Notifications\Relation\NewEmployeePosition;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NewEmployeePositionByContractorOwnerTest extends TestCase
{
    private $position;
    private $hiring_organization;
    private $contractor;
    private $role;
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create(['email_verified_at' => Carbon::now()]);
        $this->contractor = Contractor::inRandomOrder()->limit(1)->first();
        $this->role = factory(Role::class)->create([
            'user_id' => $this->user->id,
            'role' => 'employee',
            'entity_key' => 'contractor',
            'entity_id' => $this->contractor->id,

        ]);
        User::find($this->user->id)->update(['current_role_id' => $this->role->id]);
        $this->user = $this->user->refresh();

        $this->hiring_organization = factory(HiringOrganization::class)->create();
        $this->position = factory(Position::class)->create(['position_type' => 'contractor', 'hiring_organization_id' => $this->hiring_organization->id]);

        $this->assertInstanceOf(User::class, $this->user);
        $this->assertInstanceOf(Role::class, $this->role);
        $this->assertInstanceOf(Contractor::class, $this->contractor);
        $this->assertInstanceOf(HiringOrganization::class, $this->hiring_organization);
        $this->assertInstanceOf(Position::class, $this->position);
    }

    /**
     * @group Notifications
     */
    public function testQueueEmail()
    {
        Queue::fake();
        SendEmployeeNewPosition::dispatch($this->user, $this->position);
        Queue::assertPushed(SendEmployeeNewPosition::class, function ($job) {
            return $job->queue;
        });
    }

    /**
     * @group Notifications
     */
    public function testSendEmail()
    {
        if (config('app.env') != 'production') {
            SendEmployeeNewPosition::dispatch($this->user, $this->position);
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

            $user->notify(new NewEmployeePosition($this->position, $this->contractor));

            Notification::assertSentTo(
                $user,
                NewEmployeePosition::class,
                function ($notification) use ($user) {
                    $notifiable = $notification->toMail($user);
                    $this->assertEquals("Re: New Position Assigned to " . ucwords($user->first_name), $notifiable->subject);
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
