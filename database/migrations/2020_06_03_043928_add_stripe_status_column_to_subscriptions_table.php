<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStripeStatusColumnToSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            DB::beginTransaction();

            if (Schema::hasColumn('subscriptions', 'stripe_status')) {
                Schema::table('subscriptions', function (Blueprint $table) {
                    $table->dropColumn('stripe_status');
                });
            }

            Schema::table('subscriptions', function (Blueprint $table) {
                $table->string('stripe_status')->nullable();
            });

            DB::commit();

        } catch (Exception $ex) {
            DB::rollback();
            Log::debug(__METHOD__, ['exception' => $ex->getMessage()]);
            throw $ex;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('subscriptions', 'stripe_status')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropColumn('stripe_status');
            });
        }
    }
}
