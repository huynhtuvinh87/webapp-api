<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnDynamicFormColumnIdRatingCommentHiringOrganization extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hiring_organizations', function (Blueprint $table) {

            // Dropping column if it exists
            if (Schema::hasColumn('hiring_organizations', 'dynamic_form_column_id_rating_comment')) {
                Schema::table('hiring_organizations', function (Blueprint $table) {
                    $table->dropColumn('dynamic_form_column_id_rating_comment');
                });
            }

            $table->unsignedInteger('dynamic_form_column_id_rating_comment')
                ->after('dynamic_form_column_id_rating')
                ->nullable()
                ->comment("'Form column ID to read rating risk from'");

            $table->foreign('dynamic_form_column_id_rating_comment', 'ho_dynamic_form_column_id_rating_comment_foreign')
                ->references('id')
                ->on('dynamic_form_columns');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hiring_organizations', function (Blueprint $table) {
            // Dropping column if it exists
            if (Schema::hasColumn('hiring_organizations', 'dynamic_form_column_id_rating_comment')) {
                $table->dropForeign('ho_dynamic_form_column_id_rating_comment_foreign');
                $table->dropColumn('dynamic_form_column_id_rating_comment');
            }
        });
    }
}
