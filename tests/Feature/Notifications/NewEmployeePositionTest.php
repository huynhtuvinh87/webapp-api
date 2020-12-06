<?php

namespace Tests\Feature\Notifications;

use App\Jobs\Notifications\SendEmployeeNewPosition;
use App\Models\Contractor;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Notifications\Relation\NewEmployeePosition;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NewEmployeePositionTest extends TestCase
{
    public function setUp(): void
    {
        try {
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
            $this->position = Position::inRandomOrder()->where('position_type', 'employee')->where('is_active', '1')->limit(1)->first();

            $this->assertInstanceOf(User::class, $this->user);
            $this->assertInstanceOf(Contractor::class, $this->contractor);
            $this->assertInstanceOf(Role::class, $this->role);
            $this->assertInstanceOf(Position::class, $this->position);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    /**
     * @group Notifications
     */
    public function testQueue()
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
            $this->assertEquals(true, true);
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

            if (is_null($this->user) || is_null($this->position) || is_null($this->contractor)) {
                throw new Exception("User, postion or contractor not set properly");
            }

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
    public function testDeleteUser()
    {
        try {
            User::destroy($this->user->id);
            $this->assertDatabaseMissing('users', ['id' => $this->user->id]);

            $role = Role::find($this->role->id);
            $role->forceDelete();
            $this->assertDatabaseMissing('roles', ['id' => $this->role->id]);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
}
