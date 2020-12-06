<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMailNotificationTable extends Migration
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
            Schema::create('notification_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->text('notifiable_type');
                $table->unsignedInteger('notifiable_id')
                	->comment("FK to notifiable objects");
                $table->timestamp('created_at')
                    ->nullable();
                $table->timestamp('updated_at')
                    ->nullable();
                $table->text('notification')
                ->comment('Notification class that was used');
                $table->longText('data')
                ->comment('JSON result of toArray method with all data for notification');
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
        Schema::dropIfExists('notification_logs');
    }
}
