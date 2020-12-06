<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Models\DynamicForm;
use Illuminate\Support\Facades\Schema;

class SetKennametalLegendToScore extends Migration
{
    private $kennametal_form_id = 19;
    private $kennametal_ho_id = 114;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $form = DynamicForm::where('id', $this->kennametal_form_id)->first();
        if(!isset($form)){
            Log::warn("Kennametal form not found - skipping migration");
            return;
        }

        try {
            DB::beginTransaction();
            $max_order = DB::table('dynamic_form_columns')->where('dynamic_form_id', $this->kennametal_form_id)->max('order');

            // Dynamic Forms
            DB::table('dynamic_form_columns')->insert(
                array(
                    'dynamic_form_id' => $this->kennametal_form_id,
                    'label' => 'Score',
                    'type' => 'image',
                    'order' => $max_order + 1,
                    'visible_to_contractors' => 0,
                    'file_id' => 62096
                )
            );
            DB::statement("UPDATE `dynamic_form_columns` SET `visible_to_contractors` = 0 WHERE `type` = 'transformation'");

            //Folders
            DB::table('folders')->insert([
                [
                    'name' => 'JSA',
                    'hiring_organization_id' => $this->kennametal_ho_id,
                    'created_at' => '2020-01-20 00:00:00',
                    'updated_at' => '2020-01-20 00:00:00'
                ],
                [
                    'name' => 'EHS Plan',
                    'hiring_organization_id' => $this->kennametal_ho_id,
                    'created_at' => '2020-01-20 00:00:00',
                    'updated_at' => '2020-01-20 00:00:00'
                ]
            ]);

            DB::table('departments')
                ->updateOrInsert(
                    ['hiring_organization_id' => $this->kennametal_ho_id, 'name' => 'PM'],
                    ['created_at' => '2020-01-20 00:00:00', 'updated_at' => '2020-01-20 00:00:00']
                );

            $department = DB::table('departments')
                ->where('hiring_organization_id', $this->kennametal_ho_id)
                ->where('name', 'PM')
                ->first();

            if($department) {
                $folders = DB::table('folders')->where('hiring_organization_id', $this->kennametal_ho_id)->get();
                foreach ($folders as $folder) {
                    DB::table('department_folder')->insert([
                        'department_id' => $department->id,
                        'folder_id' => $folder->id,
                        'created_at' => '2020-01-20 00:00:00',
                        'updated_at' => '2020-01-20 00:00:00'
                    ]);
                }
            }

            $kennametal_owner = DB::table('roles')
                ->where('entity_id', $this->kennametal_form_id)
                ->where('entity_key', 'hiring_organization')
                ->where('role', 'owner')
                ->first();

            DB::table('department_role')
                ->updateOrInsert(
                    ['department_id' => $department->id, 'role_id' => $kennametal_owner->id],
                    ['created_at' => '2020-01-20 00:00:00']
                );

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
        try {
            DB::beginTransaction();
            Schema::table('dynamic_forms_columns', function (Blueprint $table) {
                DB::statement("UPDATE `dynamic_form_columns` SET `visible_to_contractors` = 1 WHERE `dynamic_form_id` = $this->kennametal_form_id AND `type` = 'transformation'");
                DB::statement("DELETE FROM `dynamic_form_columns` WHERE `dynamic_form_id` = $this->kennametal_form_id AND `type` = 'image'");
            });

            DB::table('department_folder')->delete();
            DB::table('folders')->delete();

            DB::commit();
        } catch (Exception $e) {
            Log::error($e);
            DB::rollback();
            throw $e;
        }
    }
}
