<?php

namespace Tests\Feature\Notifications;

use App\Jobs\QueueEmployeeWeeklyNotification;
use App\Models\User;
use App\Notifications\Requirement\WeeklyDigest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Exception;
use Log;

class DispatchEmployeeWeeklyNotificationTest extends TestCase
{
    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $past_due_requirements = DB::table('view_employee_requirements')
            ->join('positions', 'positions.id', '=', 'view_employee_requirements.position_id')
            ->where('position_type', 'employee')
            ->where('requirement_status', 'past_due')
            ->where('requirement_type', '!=', 'internal_document')
            ->inRandomOrder()
            ->limit(5)
            ->get();

        $user = $past_due_requirements->pluck('user_id')->first();

        $this->user = User::find($user);
        $this->past_due = $past_due_requirements;
    }

    /**
     * @group Notifications
     */
    public function testQueueEmail()
    {
        Queue::fake();
        QueueEmployeeWeeklyNotification::dispatch();
        Queue::assertPushed(QueueEmployeeWeeklyNotification::class, function ($job) {
            return $job->queue;
        });
    }

    /**
     * @group Notifications
     */
    public function testSendEmail()
    {
        try {
            if (config('app.env') != 'production') {
                $user = $this->user;
                $user->notify(new WeeklyDigest($this->past_due));
                $this->assertEquals(true, true);
            }
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    /**
     * @group Notifications
     */
    public function testNotification(){

        $user = $this->user;

        Notification::fake();
        $user->notify(new WeeklyDigest($this->past_due));

        Notification::assertSentTo(
            $user,
            WeeklyDigest::class,
            function ($notification) use ($user){
                $notifiable = $notification->toMail($user);
                $this->assertEquals("Re: Contractor Compliance Past Due Tasks Weekly Digest for " . ucwords($user->first_name), $notifiable->subject);
                $blade_file = "resources/views/" . str_replace(".", "/", $notifiable->view) . ".blade.php";
                $this->assertFileExists($blade_file,"Blade file found it!");
                return true;
            }
        );

    }
}
