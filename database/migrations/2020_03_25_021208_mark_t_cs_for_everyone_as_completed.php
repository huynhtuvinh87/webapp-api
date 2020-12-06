<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MarkTCsForEveryoneAsCompleted extends Migration
{

    private $tmpDate = '0001-01-01 00:00:00';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $query = "UPDATE users
            SET tc_signed_at = '$this->tmpDate'
            WHERE tc_signed_at IS NULL";
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
            SET tc_signed_at = NULL
            WHERE tc_signed_at = '$this->tmpDate'";
        $res = DB::statement($query);
    }
}
