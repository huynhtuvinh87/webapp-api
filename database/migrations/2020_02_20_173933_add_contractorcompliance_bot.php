<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

class AddContractorcomplianceBot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $user = User::create([
            'first_name' => 'Contractor Compliance',
            'last_name' => 'Bot',
            'email' => 'bot@contractorcompliance.io',
            'password' => '',
            'verified_email' => 0,
        ]);

        $role = Role::create([
            "user_id" => $user->id,
            "role" => "admin",
            "entity_key" => "hiring_organization",
            "entity_id" => 107,
        ]);

        $user->current_role_id = $role->id;
        $user->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $user = User::where("email",'bot@contractorcompliance.io')->first();
        $role = Role::where("user_id",$user->id)->first();
        $user->forceDelete();
        $role->forceDelete();
    }
}
