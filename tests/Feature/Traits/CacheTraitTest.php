<?php

namespace Tests\Feature;

use App\Lib\Services\HiringOrganizationCompliance;
use App\Models\HiringOrganization;
use App\Models\Role;
use App\Models\User;
use App\Traits\CacheTrait;
use Exception;
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as FacadesRequest;

class CacheTraitTest extends TestCase
{
    use CacheTrait;

    /**
     * Tests to see if the build tag method properly generates tags
     */
    public function testBuildTag(){
        $tag = $this->buildTag("key", "value");
        $this->assertEquals("KEY:value", $tag);
    }

    /**
     * Tests to see if buildTagFromObject works with known model
     *
     * @return void
     */
    public function testBuildTagFromObjectFromKnownModel(){
        $object = User::first();
        $tag = $this->buildTagFromObject($object);
        $this->assertEquals("USER:" . $object->id, $tag);
    }

    /**
     * Tests to see if buildTagFromObject works with unknown model
     * @expectedException Exception
     */
    public function testBuildTagFromObjectFromArray(){
        $object = [
            "id" => 1
        ];

        // This should fail as the object is an array
        $tag = $this->buildTagFromObject($object);

        // If it gets to this point, it failed
        $this->assertTrue(false);
    }

    /**
     * Tests buildKeyFromRequest
     */
    public function testBuildKeyFromRequest(){
        $requestParams = [

        ];

        // $this->json("POST", )

        $this->markTestIncomplete();
    }
}
