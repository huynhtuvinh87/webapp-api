<?php

namespace Tests\Feature\Notifications;

use App\Jobs\SendContractorWarning;
use App\Models\Contractor;
use App\Models\User;
use App\Notifications\Requirement\InWarning;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Tests the different dynamic forms that can be read
 */
class SendContractorWarningTest extends TestCase
{
//    use DatabaseTransactions;

    private $requirement;
    private $contractor;
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->queue = 'low';
        $this->connection = 'database';

        // Setup
        $this->requirement = $this->getRequirementWarning();
        $this->contractor = Contractor::find($this->requirement->contractor_id);
        $this->user = $this->contractor->owner->user;

        $this->assertInstanceOf(User::class, $this->user);
        $this->assertInstanceOf(Contractor::class, $this->contractor);
    }

    private function getRequirementWarning()
    {
        $requirement_warning = DB::table('view_contractor_requirements')
            ->where('requirement_status', 'in_warning')
            ->where('requirement_type', '!=', 'internal_document')
            ->where('warning_date', Carbon::now()->toDateString())
            ->inRandomOrder()
            ->first();

        $this->assertNotEmpty($requirement_warning, "warning requirements not found");

        return $requirement_warning;
    }

    /**
     * @group Notifications
     */
    public function testSendEmail()
    {
        if (config('app.env') != 'production') {
            SendContractorWarning::dispatch($this->contractor);
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
            $requirements = $this->gettingWarningRequirementsByContractor();

            Notification::fake();
            $user->notify(new InWarning($this->user, $requirements));

            Notification::assertSentTo(
                $user,
                InWarning::class,
                function ($notification) use ($user) {
                    $notifiable = $notification->toMail($user);
                    $this->assertEquals("Re: Mandatory Requirements Set to Expire", $notifiable->subject);
                    $blade_file = "resources/views/" . str_replace(".", "/", $notifiable->view) . ".blade.php";
                    $this->assertFileExists($blade_file, "Blade file found it!");
                    return true;
                }
            );
        } catch (Exception $e) {
            throw new Exception($e);
        }

    }

    private function gettingWarningRequirementsByContractor()
    {
        $requirements_in_warning = DB::table('view_contractor_requirements')
            ->where('requirement_status', 'in_warning')
            ->where('requirement_type', '!=', 'internal_document')
            ->where('contractor_id', $this->contractor->id)
            ->where('warning_date', Carbon::now()->toDateString())
            ->distinct()
            ->orderBy('requirement_name')
            ->get();

        $this->assertNotEmpty($requirements_in_warning, "Requirements in warning not found");

        return $requirements_in_warning;
    }

    /**
     * @group Notifications
     */
    public function testQueue()
    {
        Queue::fake();
        SendContractorWarning::dispatch($this->contractor);
        Queue::assertPushed(SendContractorWarning::class, function ($job) {
            return $job->queue;
        });
    }
}
