<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\HiringOrganization;
use App\Models\Requirement;
use App\Models\RequirementHistory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FileTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * Checking to see that the $file->requirementHistories relation works
     */
    public function testFileRequirementHistoriesRelation()
    {
        $requirementHistory = RequirementHistory::whereNotNull('file_id')
            ->get()
            ->first();
        $file = File::where('id', $requirementHistory->file_id)
            ->first();

        $histories = $file->requirementHistories;

        $this->assertNotNull($histories);
        $this->assertNotCount(0, $histories);
    }
}
