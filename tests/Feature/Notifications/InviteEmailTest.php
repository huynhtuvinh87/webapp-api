<?php

namespace Notifications;

use App\Models\Contractor;
use App\Models\HiringOrganization;
use App\Models\Requirement;
use App\Models\User;
use App\Notifications\Registration\Invite;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InviteEmailTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->token = Str::random(64);
        $this->user = factory(User::class)->create(['email_verified_at' => Carbon::now()]);
        $this->hiring_organization = HiringOrganization::inRandomOrder()->limit(1)->first();
        $this->assertInstanceOf(User::class, $this->user);
        $this->assertInstanceOf(HiringOrganization::class, $this->hiring_organization);
    }

    /**
     * @group Notifications
     */
    public function testNotification()
    {
        $user = $this->user;

        if (config('app.env') != 'production') {
            $user->notify(new Invite($this->token, $this->hiring_organization->name));
        }

        Notification::fake();

        $user->notify(new Invite($this->token, $this->hiring_organization->name));

        Notification::assertSentTo(
            $user,
            Invite::class,
            function ($notification) use ($user) {
                $notifiable = $notification->toMail($user);
                $this->assertEquals("Re: Mandatory Registration for Contractors of " . ucwords($this->hiring_organization->name), $notifiable->subject);
                $blade_file = "resources/views/" . str_replace(".", "/", $notifiable->view) . ".blade.php";
                $this->assertFileExists($blade_file, "Blade file found it!");
                return true;
            }
        );
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
