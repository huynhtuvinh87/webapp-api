<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AccessLevelNotNullableRolesTable extends Migration
{

    public function __construct(){
        //Allow editing a table with an enum column
        \Illuminate\Support\Facades\DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function(Blueprint $table){

            \Illuminate\Support\Facades\DB::table('roles')->whereNull('access_level')->update([
                'access_level' => 3
            ]);

            $table-> integer('access_level')->default(3)->nullable(false)->change();
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
