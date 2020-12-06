<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestLogTable extends Migration
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
            Schema::dropIfExists('request_logs');

            Schema::create('request_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->text("route");
                $table->string("ip_address")
                    ->comment("IP Address");
                $table->text("method")
                    ->nullable();
                $table->text("body")
                    ->nullable();
                $table->text("token")
                    ->nullable();
                $table->text("user")
                    ->nullable();

                $table->timestamps();
                // NOTE: If timestamps doesnt useCurrent as default, use the following lines instead
                // $table->timestamp('updated_at')
                //     ->useCurrent();
                // $table->timestamp('created_at')
                //     ->useCurrent();
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
        Schema::dropIfExists('request_logs');
    }
}
