<?php

use App\Models\HiringOrganization;
use App\Models\Module;
use App\Models\ModuleVisibility;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddContractorButtonModule extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// Creating module to handle the "Add Contractor" button visibility
		$module = Module::create([
			"name" => "show-add-contractor-btn",
			"visible" => true,
			"inherit" => false
		]);

		// Disabling the button for ALC
		$alcHiringOrg = HiringOrganization::find(144);
		if(isset($alcHiringOrg)){
			ModuleVisibility::create([
				"module_id" => $module->id,
				"entity_type" => "hiring_organization",
				"entity_id" => $alcHiringOrg->id,
				"visible" => false,
				"created_at" => Carbon::now(),
				"updated_at" => Carbon::now(),
			]);
		} else {
			//If the environment is not dev, then throw an exception if ALC can't be found
			if(config('app.env') != 'development'){
				throw new Exception("ALC could not be found - can't setup module properly");
			}
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$module = Module
			::where('name', 'show-add-contractor-btn')
			->first();

		// Deleting module visibilities
		$moduleVis = ModuleVisibility 
			::where('module_id', $module->id)
			->delete();

		// Deleting module
		if(isset($module)){
			$module->delete();
		}
	}
}
