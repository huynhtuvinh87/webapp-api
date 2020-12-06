<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExternalIdToRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {    
            if (!Schema::hasColumn('roles', 'external_id')) {
                Schema::table('roles', function (Blueprint $table) {
                    $table->string('external_id')->nullable()->comment('External ID used for 3rd Party API Integrations');
                });
            }
        }
        catch(Exception $ex){
            Log::debug(__METHOD__, [ 'exception' => $ex->getMessage() ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        try {
            if (Schema::hasColumn('roles', 'external_id')) {
                Schema::table('roles', function (Blueprint $table) {
                    $table->dropColumn('external_id');
                });
            }
        }
        catch (Exception $ex) {
            Log::debug(__METHOD__, [ 'exception' => $ex->getMessage() ]);
        }
        
    }
}
