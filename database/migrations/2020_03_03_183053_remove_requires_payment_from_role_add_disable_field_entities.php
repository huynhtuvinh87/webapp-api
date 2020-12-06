<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveRequiresPaymentFromRoleAddDisableFieldEntities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Dropping column requires_payment if it exists
        if (Schema::hasColumn('roles', 'requires_payment')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('requires_payment');
            });
        }

        if (Schema::hasColumn('contractors', 'owner_id')) {
            Schema::table('contractors', function (Blueprint $table) {
                $table->tinyInteger('is_active')->default(1)->after('owner_id');
            });
        } else {
            Schema::table('contractors', function (Blueprint $table) {
                $table->tinyInteger('is_active')->default(1);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->tinyInteger('requires_payment')->default(0);
        });

        // Dropping column requires_payment if it exists
        if (Schema::hasColumn('contractors', 'is_active')) {
            Schema::table('contractors', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
}
