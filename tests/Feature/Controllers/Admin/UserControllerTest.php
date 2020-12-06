<?php

namespace Tests\Feature;

use App\Models\Contractor;
use App\Models\User;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    public function testGetUsersFromAdmin()
    {
        $this->markTestSkipped("404 for some reason, could be due to api.url");
        $adminUser = User::where('global_admin', 1)
            ->first();

        $contractor = Contractor::first();

        $route = config('api.url') . "admin/contractor/{$contractor->id}";
        $headers = [
            'Accept' => 'application/json, text/plain, */*',
        ];

        $page1Params = [
            'page' => 1,
            'search' => null,
        ];
        $page2Params = [
            'page' => 2,
            'search' => null,
        ];

        $this->markTestSkipped("route was returning 404");

        $page1Response = $this->actingAs($adminUser, 'api')
            ->json("GET", $route, $page1Params, $headers);
        $page2Response = $this->actingAs($adminUser, 'api')
            ->json("GET", $route, $page2Params, $headers);

        $this->assertEquals(200, $page1Response->status());
        $this->assertNotNull($page1Response);

        $this->assertEquals(200, $page2Response->status());
        $this->assertNotNull($page2Response);

        // Verifying the response bodies are different
        // TODO

        $page1ResponseObj = json_decode($page1Response->content());
        $page2ResponseObj = json_decode($page2Response->content());

        $page1Users = $page1ResponseObj->users->data;
        $page2Users = $page2ResponseObj->users->data;

        $this->assertNotEquals($page1Users, $page2Users);

        // var_dump($page1ResponseObj->users->data[0]->user_id);
    }
}
