<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contractors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('owner_id');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('post_code')->nullable();
            $table->string('website')->nullable();
            $table->string('wsib_number')->nullable();
            $table->string('avatar')->nullable();

            // Payment Columns
            $table->string('stripe_id')->nullable()->collation('utf8mb4_bin');
            $table->string('card_brand')->nullable();
            $table->string('card_last_four')->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            // KPIs Columns
            $table->string('kpi_overall_compliance')->default('{"active: 1", "order" : 1}');
            $table->string('kpi_completed_requirements')->default('{"active: 1", "order" : 2}');
            $table->string('kpi_pending_requirements')->default('{"active: 1", "order" : 3}');
            $table->string('kpi_declined_requirements')->default('{"active: 1", "order" : 4}');

            // Vendor Number
            $table->string('vendor_number')->nullable();
            $table->date('registration_deadline')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contractors');
    }
}
