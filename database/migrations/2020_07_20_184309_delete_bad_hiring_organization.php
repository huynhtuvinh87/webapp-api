<?php

use App\Models\HiringOrganization;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteBadHiringOrganization extends Migration
{
    protected $ho_id_to_be_deleted;
    protected $ho_name_to_be_deleted;
    protected $user_email_to_be_deleted;

    public function __construct()
    {
        $this->ho_id_to_be_deleted = 153;
        $this->ho_name_to_be_deleted = "First Quantum Minerals";
        $this->user_email_to_be_deleted = "FirstQuantumMinerals@contractorcompliance.io";
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            DB::beginTransaction();

            $ho = HiringOrganization::where('id', $this->ho_id_to_be_deleted)->where('name', DB::raw("'" . $this->ho_name_to_be_deleted . "'"))->first();
            $owner = $ho->owner;
            $user = $owner->user->where('email', DB::raw("'" . $this->user_email_to_be_deleted . "'"))->first();

            Log::info("Deleting the following HO: " . json_encode($ho));
            Log::info("Deleting the following ROLE: " . json_encode($owner));
            Log::info("Deleting the following USER: " . json_encode($user));

            $user->destroy($user->id);
            $owner->destroy($owner->id);
            $ho->destroy($ho->id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            Log::debug(__METHOD__, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
