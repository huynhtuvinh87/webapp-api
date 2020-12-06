<?php

use App\Models\DynamicFormColumn;
use App\Models\DynamicFormSubmission;
use App\Models\DynamicFormSubmissionAction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DynamicFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // =============================================== //
        // TODO: Remove before releasing - ensuring that the tables do not exist to help with the migration
        $this->down();
        // =============================================== //

        // Creating the dynamic_forms table
        Schema::create('dynamic_forms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('description')
                ->nullable();
            // Columns for handling author and change information
            $table->unsignedInteger('create_role_id');
            $table->unsignedInteger('modify_role_id')->nullable();

            // ===== Foreign Keys ===== //
            $table->foreign('create_role_id')->references('id')->on('roles');
            $table->foreign('modify_role_id')->references('id')->on('roles');
            $table->timestamps();
        });

        //---------------------------------------------------------------//
        // Creating the table to keep track of the columns for the dynamic form
        Schema::create('dynamic_form_columns', function (Blueprint $table) {
            // Dynamic Form Column Types
            $controlTypes = with(new DynamicFormColumn)->getControlTypes();

            $table->increments('id');

            $table->unsignedInteger('dynamic_form_id')
                ->comment("References the dynamic_forms.id");

            $table->string('label');

            $table->string('description')->nullable();

            $table->enum('type', $controlTypes)
                ->nullable();

            $table->unsignedInteger('order')
                ->default(0)
                ->comment('order in form');

            $table->json('transformation')
                ->comment("JSON to describe transformation to obtain result. Use json-logic (https://github.com/jwadhams/json-logic-php) format")
                ->nullable();

            // TODO: Implement ordering

            // ===== Foreign Keys ===== //
            $table->foreign('dynamic_form_id')->references('id')->on('dynamic_forms');
        });

        //---------------------------------------------------------------//
        // Creating the table to keep track of form submissions
        Schema::create('dynamic_form_submissions', function (Blueprint $table) {

            $submissionStates = with(new DynamicFormSubmission)->getSubmissionStates();

            $table->increments('id');

            $table->unsignedInteger('dynamic_form_id')
                ->comment("References dynamic_forms.id");

            // Creating columns for handling author and change information
            $table->unsignedInteger('create_role_id');

            $table->unsignedInteger('modify_role_id')->nullable();
            // Column for submission model

            $table->json('dynamic_form_model')
                ->comment("Stores a copy of the columns from the form as to when the submission was made. In the event the form is changed, load this model in the columns field instead");

            $table->enum('state', $submissionStates)
                ->default('pending');

            $table->timestamps();

            // ===== Foreign Keys ===== //
            $table->foreign('create_role_id')->references('id')->on('roles');
            $table->foreign('modify_role_id')->references('id')->on('roles');
            $table->foreign('dynamic_form_id')->references('id')->on('dynamic_forms');
        });

        // Creating data table to hold key (label) / value pairs
        Schema::create('dynamic_form_submission_data', function (Blueprint $table) {
            $table->increments('id');

            $table
                ->unsignedInteger('dynamic_form_submission_id')
                ->nullable();

            // dynamic_form_column_label would normally point at the dynamic form column id. However, the dynamic form columns can be manipulated and changed. Needs to use the model stored in the dynamic_form_submissions table.
            $table
                ->string('dynamic_form_column_label');

            $table->unsignedInteger('dynamic_form_column_id')
                ->comment("references the column id. However, model is stored in submissions table")
                ->nullable();

            $table
                ->string('value')
                ->nullable();

            // ===== Foreign Keys ===== //
            $table
                ->foreign('dynamic_form_submission_id')
                ->references('id')
                ->on('dynamic_form_submissions');
            $table->foreign('dynamic_form_column_id')
                ->references('id')->on('dynamic_form_columns')
                ->onDelete('set null');
        });

        // Creating Submission Actions table
        Schema::create('dynamic_form_submission_actions', function (Blueprint $table) {
            // Getting submission actions from model
            $submissionActions = with(new DynamicFormSubmissionAction)->getActions();

            $table->increments('id');

            $table->unsignedInteger('dynamic_form_id')
                ->comment("References the dynamic_forms.id");

            $table
                ->string('dynamic_form_column_label');

            $table->unsignedInteger('dynamic_form_column_id')
                ->comment("references the dynamic form columns")
                ->nullable();

            $table->json('value')
                ->comment("Additional information to be used when processing action. Example: Notify will have a user ID")
                ->nullable();

            $table->enum('action', $submissionActions);

            // ===== Foreign Keys ===== //
            $table->foreign('dynamic_form_id')->references('id')->on('dynamic_forms');
            $table->foreign('dynamic_form_column_id')
                ->references('id')->on('dynamic_form_columns')
                ->onDelete('set null');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Dropping the Dynamic form tables
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('dynamic_forms');
        Schema::dropIfExists('dynamic_form_columns');
        Schema::dropIfExists('dynamic_form_submissions');
        Schema::dropIfExists('dynamic_form_submission_data');
        Schema::dropIfExists('dynamic_form_submission_actions');
        Schema::dropIfExists('dynamic_form_post_submission_actions');
        Schema::enableForeignKeyConstraints();
    }
}
