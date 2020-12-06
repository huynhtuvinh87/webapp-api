<?php

use App\Jobs\MoveFileJob;
use App\Models\File;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ImplementUserAvatarFiles extends Migration
{
    private $avatarFileIdColumnName = 'avatar_file_id';

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
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', $this->avatarFileIdColumnName)) {
                    Schema::table('users', function (Blueprint $table) {
                        $table->dropColumn($this->avatarFileIdColumnName);
                    });
                }

                $table->unsignedInteger($this->avatarFileIdColumnName)
                    ->comment("file ID for avatar")
                    ->nullable();
            });

            // Move avatar images to files
            if (isset($this->sampleSize)) {
                $allUsers = User::limit(100)
                    ->whereNotNull('avatar')
                    ->where('avatar', '<>', '')
                    ->get();
            } else {
                $allUsers = User::whereNotNull('avatar')
                    ->where('avatar', "<>", '')
                    ->get();
            }

            $countFilesToMove = sizeof($allUsers);
            $countFilesMoved = 0;
            $countFilesNotFound = 0;

            Log::info("Files to Move: $countFilesToMove");

            foreach ($allUsers as $user) {
                try {

                    // If they have an avatar, try to create a file
                    $hasAvatar = isset($user->avatar);
                    $fileExists = false;

                    if ($hasAvatar) {
                        $fileExists = Storage::disk('public')->exists($user->avatar);
                    }

                    if (!$fileExists) {
                        $countFilesNotFound++;
                        Log::debug("Avatar file not found", [
                            'avatar' => $user->avatar,
                        ]);
                    }

                    // TODO: Check if file exists as well
                    if ($fileExists) {
                        $role = $user->role;
                        if (!isset($role)) {
                            $role = $user->highestRole;
                        }

                        if (isset($role)) {
                            $newFile = File::create([
                                'name' => null,
                                'path' => $user->avatar,
                                'ext' => $user->avatar_file_ext,
                                'role_id' => $role->id,
                                'ip' => null,
                                'disk' => 'public',
                                'visibility' => 'public',
                                'created_at' => isset($user->created_at) ? $user->created_at : null,
                            ]);

                            // Try updating date
                            try {
                                if (isset($user->created_at)) {
                                $newFile->update([
                                    'created_at' => $user->created_at,
                                ]);
                                }
                            } catch (Exception $e) {
                                // Do nothing - not important
                                Log::debug("Failed to update created_at for file", [
                                    "Original created at" => $user->created_at,
                                ]);
                            }
                            $newFile->updateFromPath();

                            if (!isset($newFile->id)) {
                                throw new Exception("File ID was not defined - failed to create file for avatar");
                            }

                            User::where('id', $user->id)
                                ->update([
                                    'avatar_file_id' => $newFile->id,
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
                    }
                } catch (Exception $e) {
                    Log::error($e->getMessage());
                }
            }

            // Dropping columns
            // $this->dropColumnIfExists('users', 'avatar');
            // $this->dropColumnIfExists('users', 'avatar_file_name');
            // $this->dropColumnIfExists('users', 'avatar_file_ext');

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
        try {

            DB::beginTransaction();

            if (Schema::hasColumn('users', $this->avatarFileIdColumnName)) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn($this->avatarFileIdColumnName);
                });
            }

            DB::commit();

        } catch (Exception $e) {
            Log::error($e);
            DB::rollback();
            throw $e;
        }
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
