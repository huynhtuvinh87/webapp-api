<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFortisToModulesTable extends Migration
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
            
            DB::statement("INSERT INTO modules (name, visible)
            SELECT * FROM (SELECT 'hard-deadline', 0) AS tmp
            WHERE NOT EXISTS (
                SELECT name FROM modules WHERE name = 'hard-deadline'
            ) LIMIT 1;");

            // Fortis
            DB::statement("INSERT INTO module_visibilities (module_id,entity_type,entity_id,visible)
            SELECT * FROM (SELECT 2, 'hiring_organization', 64, 1) AS tmp
            WHERE NOT EXISTS (
                SELECT entity_id FROM module_visibilities WHERE entity_id = 64
            ) LIMIT 1;");

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
     * @throws Exception
     */
    public function down()
    {
        
    }
}
