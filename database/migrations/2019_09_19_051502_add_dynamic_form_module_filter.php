<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Module;

class AddDynamicFormModuleFilter extends Migration
{
    public $dynamicFormModuleName = 'dynamic-forms';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('modules')->insert([
            'name' => $this->dynamicFormModuleName,
            'visible' => false
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Module::where('name', $this->dynamicFormModuleName)->delete();
    }
}
