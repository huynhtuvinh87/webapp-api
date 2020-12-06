<?php

namespace Tests\Feature\Notifications;

use App\Models\HiringOrganization;
use App\Models\Position;
use App\Models\User;
use App\Notifications\Relation\NewContractorCorporatePosition;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NewContractorCorporatePositionTest extends TestCase
{
    public function setUp() : void
    {
        try {
            parent::setUp();
            $this->user = factory(User::class)->create(['email_verified_at' => Carbon::now()]);
            $this->hiring_organization = HiringOrganization::inRandomOrder()->limit(1)->first();
            $this->position = Position::inRandomOrder()->where('position_type', 'contractor')->where('is_active', '1')->limit(1)->first();

            $this->assertInstanceOf(User::class, $this->user);
            $this->assertInstanceOf(HiringOrganization::class, $this->hiring_organization);
            $this->assertInstanceOf(Position::class, $this->position);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    /**
     * @group Notifications
     */
    public function testNotification()
    {
        try {
            $user = $this->user;

            if (config('app.env') != 'production') {
                $user->notify(new NewContractorCorporatePosition($this->position, $this->hiring_organization));
            }

            Notification::fake();

            $user->notify(new NewContractorCorporatePosition($this->position, $this->hiring_organization));

            Notification::assertSentTo(
                $user,
                NewContractorCorporatePosition::class,
                function ($notification) use ($user) {
                    $notifiable = $notification->toMail($user);
                    $this->assertEquals("Re: New Position Assigned to You!", $notifiable->subject);
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
