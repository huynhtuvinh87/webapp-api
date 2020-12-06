<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SchemaRevisionsRound2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function($table){
            $table->datetime('tc_signed_at')->nullable();
            $table->tinyInteger('user_previewer')->default(0);
            $table->tinyInteger('receive_email_status')->default(1);
            $table->string('secondary_email')->nullable();
            $table->dropColumn('username');
        });

        Schema::table('hiring_organizations', function($table){
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('website')->nullable();
            $table->string('avatar')->nullable();
            $table->tinyInteger('wsib_required')->default(0);
            $table->tinyInteger('vendor_required')->default(0);
        });

        Schema::table('contractor_hiring_organizations', function($table){
            $table->string('vendor_number')->nullable()->change();
        });

        Schema::table('facilities', function($table){
            $table->string('notification_email')->nullable()->change();
        });

        DB::statement('alter table requirements drop column status');
        DB::statement('alter table requirements drop column type');


        Schema::table('requirements', function($table){
           $table->dropColumn('uploader_id');
           $table->string('type');
           $table->text('content')->nullable()->change();
           $table->string('content_url')->nullable()->change();
           $table->string('content_file')->nullable()->change();
        });

        Schema::table('requirements', function($table){
            $table->dropColumn('due_date');
        });

        Schema::table('positions', function($table){
            $table->tinyInteger('auto_assign')->default(0);
        });

        Schema::table('position_requirement', function($table){
            $table->tinyInteger('is_active')->default(1);
        });

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
}
