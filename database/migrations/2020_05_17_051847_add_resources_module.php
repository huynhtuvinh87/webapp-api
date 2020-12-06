<?php

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;

class AddResourcesModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public $resourceModuleName = 'resources';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {

            $module = Module::where('name', '=', $this->resourceModuleName)->first();
            $moduleId = $module->id;
            if ($moduleId === null) {
                $moduleId = DB::table('modules')->insertGetId([
                    'name' => $this->resourceModuleName,
                    'visible' => false,
                ]);
            }

            $alcHiringOrg = DB::table('hiring_organizations')->where('name', '=', 'ALC Schools')->first();
            if (!isset($alcHiringOrg))
            {
                Log::warn("ALC schools was not found when migrating");
                return;
            }
            //Add visibility for ALC
            DB::table('module_visibilities')->insert([
                'module_id' => $moduleId,
                'entity_type' => 'hiring_organization',
                'entity_id' => $alcHiringOrg->id,
                'visible' => 1,
            ]);

            $alcContractors = DB::table('contractor_hiring_organization')->where('hiring_organization_id', $alcHiringOrg->id)->get();
            foreach ($alcContractors as $contractor) {
                DB::table('module_visibilities')->insert([
                    'module_id' => $moduleId,
                    'entity_type' => 'contractor',
                    'entity_id' => $contractor->contractor_id,
                    'visible' => true,
                ]);
            }

        } catch (Exception $ex) {
            Log::error($ex);
            DB::rollback();
            throw $ex;
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Module::where('name', $this->resourceModuleName)->delete();
    }

}
