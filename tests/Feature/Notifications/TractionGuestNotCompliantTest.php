<?php

namespace Notifications;

use App\Models\Contractor;
use App\Models\HiringOrganization;
use App\Models\User;
use App\Notifications\Integration\TractionGuestNotCompliantEmployee;
use App\Notifications\Integration\TractionGuestNotCompliantHost;
use App\ViewModels\ViewContractorRequirements;
use App\ViewModels\ViewEmployeeRequirements;
use Carbon\Carbon;
use Exception;
use Faker\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TractionGuestNotCompliantTest extends TestCase
{
    public function setUp(): void
    {
        try {
            parent::setUp();
            $faker = Factory::create();

            $this->user = factory(User::class)->create(['email_verified_at' => Carbon::now()]);

            $this->hiring_organization = HiringOrganization::inRandomOrder()->limit(1)->first();
            $this->contractor = Contractor::inRandomOrder()->limit(1)->first();

            $this->corporate_requirement_past_due = DB::table('view_contractor_requirements')
                ->inRandomOrder()
                ->select('requirement_id', 'requirement_name', 'position_name')
                ->where('requirement_status', 'past_due')
                ->where('requirement_type', '!=', 'internal_document')
                ->groupBy('requirement_id', 'requirement_name', 'position_name')
                ->limit(rand(0,15))
                ->get();

            $this->employee_requirement_past_due = DB::table('view_employee_requirements')
                ->inRandomOrder()
                ->select('requirement_id', 'requirement_name', 'position_name')
                ->where('requirement_status', 'past_due')
                ->where('requirement_type', '!=', 'internal_document')
                ->groupBy('requirement_id', 'requirement_name', 'position_name')
                ->limit(rand(0,15))
                ->get();

            $this->assertInstanceOf(User::class, $this->user);
            $this->assertInstanceOf(HiringOrganization::class, $this->hiring_organization);
            $this->assertInstanceOf(Contractor::class, $this->contractor);
            if(count($this->corporate_requirement_past_due)) {
                $this->assertInstanceOf(Collection::class, $this->corporate_requirement_past_due);
            }
            if(count($this->employee_requirement_past_due)) {
                $this->assertInstanceOf(Collection::class, $this->employee_requirement_past_due);
            }

            $this->response = [
                'compliance' => [
                    'employee' => $faker->numberBetween($min=1,$max=100),
                    'contractor' => $faker->numberBetween($min=1,$max=100)
                ],
                'compliance_message' => $faker->sentence(25),
                'requirements' => [
                    'corporate' => $this->corporate_requirement_past_due,
                    'employee' => $this->employee_requirement_past_due,
                ]
            ];

            $this->mydata = [
                'hiring_organization' => $this->hiring_organization,
                'hiring_organization_user' => $this->hiring_organization->owners,
                'contractor' => $this->contractor,
                'contractor_user' => $this->user,
                'corporate_requirements' => $this->corporate_requirement_past_due,
                'employee_requirements' => $this->employee_requirement_past_due,
                'response' => $this->response
            ];
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    /**
     * @group Notifications
     */
    public function testHostNotification()
    {
        try {
            $user = $this->user;

            if (config('app.env') != 'production') {
                $user->notify(new TractionGuestNotCompliantHost($this->mydata));
            }

            Notification::fake();

            $user->notify(new TractionGuestNotCompliantHost($this->mydata));

            Notification::assertSentTo(
                $user,
                TractionGuestNotCompliantHost::class,
                function ($notification) use ($user) {
                    $notifiable = $notification->toMail($user);
                    $this->assertEquals("Re: Contractor Not Compliant", $notifiable->subject);
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
    public function testEmployeeNotification()
    {
        try {
            $user = $this->user;

            if (config('app.env') != 'production') {
                $user->notify(new TractionGuestNotCompliantEmployee($this->mydata));
            }

            Notification::fake();

            $user->notify(new TractionGuestNotCompliantEmployee($this->mydata));

            Notification::assertSentTo(
                $user,
                TractionGuestNotCompliantEmployee::class,
                function ($notification) use ($user) {
                    $notifiable = $notification->toMail($user);
                    $this->assertEquals("Re: Employee Not Compliant", $notifiable->subject);
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
        try{
            User::destroy($this->user->id);
            $this->assertDatabaseMissing('users', ['id'=>$this->user->id]);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
}
