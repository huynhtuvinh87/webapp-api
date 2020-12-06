<?php

use App\Jobs\MoveFileJob;
use App\Models\File;
use App\Models\RequirementContent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

// use Log;
// use Exception;

class ImplementRequirmentContentFiles extends Migration
{
    private $sampleSize = null;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Log::info(__METHOD__);
        try {
            DB::beginTransaction();

            // Create file_id column in users table
            Schema::table('requirement_contents', function (Blueprint $table) {
                // Dropping column if it already exists
                if (Schema::hasColumn('requirement_contents', 'file_id')) {
                    Schema::table('requirement_contents', function (Blueprint $table) {
                        $table->dropColumn('file_id');
                    });
                }

                $table->unsignedInteger('file_id')
                    ->nullable();
            });

            // Moving all files over
            if (isset($this->sampleSize)) {
                $allReqContents = RequirementContent::limit(100)
                    ->whereNotNull('file')
                    ->where('file', '<>', '')
                    ->get();
            } else {
                $allReqContents = RequirementContent::whereNotNull('file')
                    ->where('file', '<>', '')
                    ->get();
            }

            $countFilesToMove = sizeof($allReqContents);
            $countFilesMoved = 0;
            $countFilesNotFound = 0;

            Log::info("Files to Move: $countFilesToMove");

            foreach ($allReqContents as $reqContents) {
                try {
                    $hasFile = $reqContents->file != null;
                    $fileExists = false;

                    if ($hasFile) {
                        $fileExists = Storage::disk('public')->exists($reqContents->file);
                    }

                    if (!$fileExists) {
                        $countFilesNotFound++;
                        Log::info("Requirement content file not found", [
                            'file' => $reqContents->file,
                        ]);
                    }

                    // If file exists on the drive...
                    if ($fileExists) {
                        // Getting requirement hiring org owner role
                        $role = $reqContents->requirement->hiringOrganization->owner;
                        if (!isset($role)) {
                            throw new Exception("Could not find owner for associated Hiring Org");
                        }

                        //     if (isset($role)) {
                        $newFile = File::create([
                            'name' => null,
                            'path' => $reqContents->file,
                            'role_id' => $role->id,
                            'ip' => null,
                            'disk' => 'public',
                            'visibility' => 'public',
                        ]);

                        try {
                            if (isset($reqContents->created_at)) {
                            $newFile->update([
                                'created_at' => $reqContents->created_at,
                            ]);
                            }
                        } catch (Exception $e) {
                            // Do nothing - not important
                            Log::debug("Failed to update created_at for file", [
                                "Original created at" => $reqContents->created_at,
                            ]);
                        }

                        $newFile->updateFromPath();

                        if (!isset($newFile->id)) {
                            throw new Exception("File ID was not defined - failed to create file for avatar");
                        }

                        RequirementContent::where('id', $reqContents->id)
                            ->update([
                                'file_id' => $newFile->id,
                            ]);

                        try {
                            MoveFileJob::dispatch($newFile, [
                                'path' => null,
                                'disk' => null,
                            ])
                                ->delay(now()->addMinutes(10));
                            $countFilesMoved++;
                        } catch (Exception $e) {
                            Log::warn($e->getMessage());
                        }
                    }
                } catch (Exception $e) {
                    Log::error($e->getMessage());
                }
            }

            // Dropping columns
            // NOTE: file column needs to be removed - causing issues with RequirementHistory->file() call
            $this->dropColumnIfExists('requirement_contents', 'file');
            $this->dropColumnIfExists('requirement_contents', 'file_name');
            $this->dropColumnIfExists('requirement_contents', 'file_ext');

            DB::commit();
        } catch (Exception $e) {
            Log::error($e);
            DB::rollback();
            throw $e;
        }

        Log::info("Files Moved: $countFilesMoved");
        Log::info("Files not Found: $countFilesNotFound");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    public function dropColumnIfExists($tableName, $columnName)
    {

        Schema::table($tableName, function (Blueprint $table) use ($columnName, $tableName) {
            // Dropping column if it already exists
            if (Schema::hasColumn($tableName, $columnName)) {
                Schema::table($tableName, function (Blueprint $table) use ($columnName) {
                    $table->dropColumn($columnName);
                });
            }
        });
    }
}
