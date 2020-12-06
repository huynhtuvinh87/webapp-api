<?php

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;

class AddSupportUserAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // NOTE: Can't use updateOrCreate.
        // updateOrCreate takes in 2 params: What to use to find existing records, then what additional information to use to update the record.
        // This should only use the email param to check to see if the account exists.
        // If the email exists, then set everything. Otherwise, only update the necessary fields.

        // Use only the email to check to see if the account exists.
        $existingUser = User::where('email', config('api.success_email')[0])->first();
        if (!isset($existingUser)) {
            // If the account doesn't exist, populate everything
            User::create([
                "email" => config('api.success_email')[0],
                "first_name" => "Success",
                "last_name" => "User",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now(),
                "password" => "No password - this account should only be used for emails",
                "global_admin" => false,
                "verified_email" => true,
                "email_verified_at" => Carbon::now(),
            ]);
        } else {
            // If the user does exist, just update the necessary fields
            Log::warn(config('api.success_email')[0] . " already exists.");
            $existingUser
                ->update([
                    "email_verified_at" => Carbon::now(),
                    "updated_at" => Carbon::now(),
                    "verified_email" => true,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // If rolling back, dont remove the user - won't be able to determine if it was created by the migration or not
        // Adjusted the logic so it would just log a message if the user account already exists.
    }
}
