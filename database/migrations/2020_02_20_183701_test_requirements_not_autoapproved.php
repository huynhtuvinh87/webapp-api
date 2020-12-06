<?php

use App\Models\Requirement;
use Illuminate\Database\Migrations\Migration;
use App\Models\Test;

class TestRequirementsNotAutoapproved extends Migration
{
    // 7 = Hiram Walker
    // 32 = Senior Aerospace
    // 39 = Dean Foods form
    // 34 = Idahoan
    // 24,25,27,28,30,23 = Sofina
    public $forms_to_be_changed = [7,32,39,34,24,25,27,28,30,23];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Requirement::whereIn('integration_resource_id', $this->forms_to_be_changed)->update(['count_if_not_approved' => 0]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Requirement::whereIn('integration_resource_id', $this->forms_to_be_changed)->update(['count_if_not_approved' => 1]);
    }
}
