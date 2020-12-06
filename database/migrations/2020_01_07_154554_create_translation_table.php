<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslationTable extends Migration
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

            Schema::dropIfExists('translations');

            Schema::create('translations', function (Blueprint $table) {
                $table->increments('id');
                $table->text('source_text')
                    ->comment('Text being translated');
                $table->text('source_lang')
                    ->nullable()
                    ->default(null)
                    ->comment('Language of the text being translated');
                $table->text('target_text')
                    ->nullable()
                    ->comment("Translated text");
                $table->text('target_lang')
                    ->nullable()
                    ->comment("Translation language");
                $table->text('reference')
                    ->nullable()
                    ->comment("Where the translation was from. manual: Manually entered, {name}: Translator, {website}: API");
                $table->text('environment')
                    ->nullable()
                    ->comment("Environment the translation was conducted. production/development");

                $table->timestamps();
                $table->timestamp('deleted_at')
                    ->nullable();
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
        Schema::dropIfExists('translations');
    }
}
