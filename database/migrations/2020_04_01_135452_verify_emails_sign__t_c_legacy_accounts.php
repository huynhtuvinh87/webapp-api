<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class VerifyEmailsSignTCLegacyAccounts extends Migration
{

    private $tcPlaceholderDate = '0001-01-01 00:00:00';
    private $emailPlaceholderDate = '2000-01-01 00:00:00';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $query = "UPDATE users
            SET tc_signed_at = '$this->tcPlaceholderDate', email_verified_at = '$this->emailPlaceholderDate'
            WHERE tc_signed_at IS NULL
            AND created_at < '2020-03-25 15:29:04'";
        $res = DB::statement($query);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $query = "UPDATE users
            SET tc_signed_at = NULL, email_verified_at = NULL 
            WHERE tc_signed_at = '$this->tcPlaceholderDate'
            AND created_at < '2020-03-25 15:29:04' AND created_at > '2020-03-25 00:00:00'";
        $res = DB::statement($query);
    }
}
