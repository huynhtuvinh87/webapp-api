<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IpGeoLocationTable extends Migration
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

            // While working on migrations, the restore command doesn't automatically remove the tables. This ensures it is removed initially.
            Schema::dropIfExists('ip_geolocations');

            // Creating files table
            Schema::create('ip_geolocations', function (Blueprint $table) {

                $table->bigIncrements('id');

                $table->string("ip_address")
                    ->comment("IP Address");

                $table->string('country_code')
                    ->comment("ISO 3166 Standards");

                $table->string('source')
                    ->nullable()
                    ->comment("Where the information was obtained from");

                $table->timestamp('updated_at')
                    ->useCurrent();

                $table->timestamp('created_at')
                    ->useCurrent();
            });

            DB::commit();
        } catch (Exception $e) {
            Log::error($e);
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ip_geolocations');
    }
}
