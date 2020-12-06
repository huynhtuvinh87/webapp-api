<?php

namespace Tests\Feature\Notifications;

use App\Models\Contractor;
use App\Models\Requirement;
use App\Models\User;
use App\Notifications\Requirement\Declined;
use Carbon\Carbon;
use Exception;
use Faker\Factory;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RequirementsTest extends TestCase
{
    public function setUp(): void
    {
        try {
            parent::setUp();
            $this->faker = Factory::create();

            $this->user = factory(User::class)->create(['email_verified_at' => Carbon::now()]);
            $this->contractor = Contractor::inRandomOrder()->limit(1)->first();
            $this->requirement = Requirement::inRandomOrder()->limit(1)->first();

            $this->assertInstanceOf(User::class, $this->user);
            $this->assertInstanceOf(Contractor::class, $this->contractor);
            $this->assertInstanceOf(Requirement::class, $this->requirement);
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
                $user->notify(new Declined($this->contractor, $this->requirement, $this->faker->sentence(25)));
            }

            Notification::fake();

            $user->notify(new Declined($this->contractor, $this->requirement, $this->faker->sentence(25)));

            Notification::assertSentTo(
                $user,
                Declined::class,
                function ($notification) use ($user) {
                    $notifiable = $notification->toMail($user);

                    $subjectLine = "Re: Declined Requirement";
                    if (isset($this->contractor->owner->user->first_name) && trim($this->contractor->owner->user->first_name) != '') {
                        $subjectLine .= " for " . $this->contractor->owner->user->first_name;
                    }

                    $this->assertEquals($subjectLine, $notifiable->subject);
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
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
}
