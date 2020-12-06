<?php

namespace Tests\Feature\Notifications;

use App\Jobs\QueueContractorWeeklyNotification;
use App\Models\Contractor;
use App\Notifications\Requirement\WeeklyDigest;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Log;
use Tests\TestCase;

class DispatchContractorWeeklyNotification extends TestCase
{
    public $contractor;
    public $past_due;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->queue = 'low';
        $this->connection = 'database';

        $past_due_requirements = DB::table('view_contractor_requirements')
            ->join('positions', 'positions.id', '=', 'view_contractor_requirements.position_id')
            ->where('position_type', 'contractor')
            ->where('requirement_status', 'past_due')
            ->where('requirement_type', '!=', 'internal_document')
            ->inRandomOrder()
            ->limit(5)
            ->get();

        $contractor_id = $past_due_requirements->pluck('contractor_id')->first();

        $this->contractor = Contractor::find($contractor_id);
        Log::info("Denis");
        $this->past_due = $past_due_requirements;
    }

    /**
     * @group Notifications
     */
    public function testQueueEmail()
    {
        Queue::fake();
        QueueContractorWeeklyNotification::dispatch();
        Queue::assertPushed(QueueContractorWeeklyNotification::class, function ($job) {
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
    public function testNotification()
    {

        $user = $this->user;

        Notification::fake();
        $user->notify(new WeeklyDigest($this->past_due));

        Notification::assertSentTo(
            $user,
            WeeklyDigest::class,
            function ($notification) use ($user) {
                $notifiable = $notification->toMail($user);
                $this->assertEquals("Re: Contractor Compliance Past Due Tasks Weekly Digest for " . ucwords($user->first_name), $notifiable->subject);
                $blade_file = "resources/views/" . str_replace(".", "/", $notifiable->view) . ".blade.php";
                $this->assertFileExists($blade_file, "Blade file found it!");
                return true;
            }
        );

    }
}
