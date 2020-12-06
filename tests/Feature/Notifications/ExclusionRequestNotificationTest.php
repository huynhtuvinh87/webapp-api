<?php

namespace Notifications;

use App\Models\HiringOrganization;
use App\Models\Requirement;
use App\Models\User;
use App\Notifications\Requirement\ExclusionApproved;
use App\Notifications\Requirement\ExclusionDeclined;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;


class ExclusionRequestNotificationTest extends TestCase
{

    public function setUp() : void
    {
        parent::setUp();
        $this->user = factory(User::class)->create(['email_verified_at' => Carbon::now()]);
        $this->requirement = Requirement::inRandomOrder()->limit(1)->first();
        $faker = Factory::create();
        $this->note = $faker->sentence(16);
        $this->hiring_organization = HiringOrganization::inRandomOrder()->limit(1)->first();

        $this->assertInstanceOf(User::class, $this->user);
        $this->assertInstanceOf(Requirement::class, $this->requirement);
        $this->assertInstanceOf(HiringOrganization::class, $this->hiring_organization);
    }

    /**
     * @group Notifications
     */
    public function testExclusionRequestDeclinedNotification(){

        $user = $this->user;

        if (config('app.env') != 'production'){
            $this->user->notify(new ExclusionDeclined($this->requirement, $this->note, $this->hiring_organization->id));
            $this->assertEquals(true, true);
        }

        Notification::fake();
        $user->notify(new ExclusionDeclined($this->requirement, $this->note, $this->hiring_organization->id));

        Notification::assertSentTo(
            $user,
            ExclusionDeclined::class,
            function ($notification) use ($user){
                $notifiable = $notification->toMail($user);
//                Log::info(json_encode($notifiable));
                $this->assertEquals("Re: Exclusion Request Declined", $notifiable->subject);
                $blade_file = "resources/views/" . str_replace(".", "/", $notifiable->view) . ".blade.php";
                $this->assertFileExists($blade_file,"Blade file found it!");
                return true;
            }
        );
	}

    /**
     * @group Notifications
     */
    public function testExclusionRequestApprovedNotification(){

        $user = $this->user;

        if (config('app.env') != 'production'){
            $this->user->notify(new ExclusionApproved($this->requirement, $this->note, $this->hiring_organization->id));
            $this->assertEquals(true, true);
        }

        Notification::fake();
        $user->notify(new ExclusionApproved($this->requirement, $this->note, $this->hiring_organization->id));

        Notification::assertSentTo(
            $user,
            ExclusionApproved::class,
            function ($notification) use ($user){
                $notifiable = $notification->toMail($user);
//                Log::info(json_encode($notifiable));
                $this->assertEquals("Re: Exclusion Request Approved", $notifiable->subject);
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
