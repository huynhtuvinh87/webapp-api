<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\HiringOrganization;
use App\Models\Requirement;
use App\Models\RequirementHistory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FileVisibilityTest extends TestCase
{
    use DatabaseTransactions;

    private static $isInit = false;

    private static $file = null;

    private static $hiringOrg = null;
    private static $hiringOrgOwnerRole = null;

    private static $contractor = null;
    private static $contractorOwnerRole = null;
    private static $contractorEmployeeRole = null;

    private static $requirement = null;
    private static $requirementHistory = null;

    public function setUp(): void
    {
        parent::setUp();
        if (!static::$isInit) {
    //         echo("Setting up");
    //         // NOTE: using Hiring org 144 as they have contractors with employees
    //         // NOTE: 144 = ALC Schools
            static::$hiringOrg = HiringOrganization::where('id', 144)
                ->first();
            static::$hiringOrgOwnerRole = static::$hiringOrg
                ->owner
                ->first();

            static::$contractor = static::$hiringOrg->contractors
                ->first();
            static::$contractorOwnerRole = static::$contractor->owner;
            static::$contractorEmployeeRole = static::$contractor->roles
                ->where('role', 'employee')
                ->first();

            // Creating internal doc file
            static::$file = factory(File::class)->create([
                'visibility' => 'private',
                'role_id' => static::$hiringOrgOwnerRole,
            ]);

            // Creating requirement
            static::$requirement = factory(Requirement::class)->create([
                'hiring_organization_id' => static::$hiringOrg->id,
                'type' => 'internal_document',
            ]);

            // Creating requirement history to attach file to
            static::$requirementHistory = factory(RequirementHistory::class)->create([
                'requirement_id' => static::$requirement->id,
                'file_id' => static::$file->id,
            ]);
        }
    }

    public function testExample(){
        $this->assertTrue(true);
    }

    /**
     * @group InternalDocuments
     * @return void
     */
    public function testCanReadPrivateFileInternalDocument()
    {
        // Test to see a hiring org owner can see the file
        $hiringOrgOwnerVis = static::$file->canReadFile(static::$hiringOrgOwnerRole);
        $this->assertTrue($hiringOrgOwnerVis);

        // Test to see a contractor owner cannot see the file
        $contractorOwnerVis = static::$file->canReadFile(static::$contractorOwnerRole);
        $this->assertFalse($contractorOwnerVis);

        // Test to see a contractor employee cannot see the file
        $contractorEmployeeVis = static::$file->canReadFile(static::$contractorEmployeeRole);
        $this->assertFalse($contractorEmployeeVis);
    }

    /**
     * @doesNotPerformAssertions
     *
     * @return void
     */
    public function testEmployeeVisFromHiringOrgUploadDataProvider()
    {
        return [
            [false, 'public', 'internal_document'],
            [false, 'private', 'internal_document'],
            [false, 'private', 'upload'],
            [false, 'private', 'upload_date'],
            [true, 'public', 'upload'],
            [true, 'public', 'upload_date'],
        ];
    }

    /**
     * Test the visibility of files in the different requirement configurations
     *
     * @group InternalDocuments
     * @dataProvider testEmployeeVisFromHiringOrgUploadDataProvider
     */
    public function testEmployeeVisFromHiringOrgUpload($expectedCanRead, $fileVis, $requirementType)
    {
        // Creating internal doc file
        static::$file->update([
            'visibility' => $fileVis,
            'role_id' => static::$hiringOrgOwnerRole->id
        ]);

        // Creating requirement
        static::$requirement->update([
            'type' => $requirementType,

        ]);

        $contractorEmployeeVis = static::$file->canReadFile(static::$contractorEmployeeRole);

        $this->assertEquals($expectedCanRead, $contractorEmployeeVis, static::$file->visibility . ' ' . static::$requirement->type . ' file visibility should be ' . ($expectedCanRead ? 'true' : 'false'));
    }




    /**
     * @doesNotPerformAssertions
     *
     * @return void
     */
    public function testEmployeeVisFromSelfUploadDataProvider()
    {
        return [
            [true, 'public', 'internal_document'],
            [true, 'private', 'internal_document'],
            [true, 'private', 'upload'],
            [true, 'private', 'upload_date'],
            [true, 'public', 'upload'],
            [true, 'public', 'upload_date'],
        ];
    }

    /**
     * Test the visibility of files in the different requirement configurations
     *
     * @group InternalDocuments
     * @dataProvider testEmployeeVisFromSelfUploadDataProvider
     */
    public function testEmployeeVisFromSelfUpload($expectedCanRead, $fileVis, $requirementType)
    {
        // Creating internal doc file
        static::$file->update([
            'visibility' => $fileVis,
            'role_id' => static::$contractorEmployeeRole->id
        ]);

        // Creating requirement
        static::$requirement->update([
            'type' => $requirementType,

        ]);

        $contractorEmployeeVis = static::$file->canReadFile(static::$contractorEmployeeRole);

        $this->assertEquals($expectedCanRead, $contractorEmployeeVis, static::$file->visibility . ' ' . static::$requirement->type . ' file visibility should be ' . ($expectedCanRead ? 'true' : 'false'));
    }
}
