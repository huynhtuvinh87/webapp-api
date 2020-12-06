<?php

use App\Models\Contractor;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RemoveRelationFromContractorsAndRolesFromPositionsOfHoTheyArentConnectedTo extends Migration
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

            Schema::table('contractor_position', function (Blueprint $table) {
                $table->timestamp('deleted_at')->nullable()->default(null);
            });
            Schema::table('position_role', function (Blueprint $table) {
                $table->timestamp('deleted_at')->nullable()->default(null);
            });
            Schema::table('contractor_facility', function (Blueprint $table) {
                $table->timestamp('deleted_at')->nullable()->default(null);
            });

            Contractor::chunk(500, function ($contractors) {
                foreach ($contractors as $contractor) {
                    $hos = $contractor->hiringOrganizations;

                    if (count($hos)) {

                        $do_not_delete_these_positions = [];
                        $do_not_delete_these_facilities = [];
                        foreach ($hos as $ho) {
                            foreach ($ho->positions as $position) {
                                $do_not_delete_these_positions[] = $position->id;
                            }
                            foreach ($ho->facilities as $facility) {
                                $do_not_delete_these_facilities[] = $facility->id;
                            }
                        }

                        Log::info("Contractor: ", [$contractor->id]);
                        Log::info("DONT delete these positions: ", $do_not_delete_these_positions);
                        Log::info("DONT delete these facilities: ", $do_not_delete_these_facilities);

                        DB::table('contractor_position')
                            ->where('contractor_id', $contractor->id)
                            ->whereNotIn('position_id', $do_not_delete_these_positions)
                            ->update(['deleted_at' => Carbon::now()->toDateString()]);

                        $roles = $contractor->roles;

                        foreach ($roles as $role) {
                            DB::table('position_role')
                                ->where('role_id', $role->id)
                                ->whereNotIn('position_id', $do_not_delete_these_positions)
                                ->update(['deleted_at' => Carbon::now()->toDateString()]);
                        }

                        DB::table('contractor_facility')
                            ->where('contractor_id', $contractor->id)
                            ->whereNotIn('facility_id', $do_not_delete_these_facilities)
                            ->update(['deleted_at' => Carbon::now()->toDateString()]);

                    } else {
                        Log::info("Contractor $contractor->id doesnt have HOs associated with it, deleting all positions & facilities");

                        DB::table('contractor_position')
                            ->where('contractor_id', $contractor->id)
                            ->update(['deleted_at' => Carbon::now()->toDateString()]);

                        $roles = $contractor->roles;

                        foreach ($roles as $role) {
                            DB::table('position_role')
                                ->where('role_id', $role->id)
                                ->update(['deleted_at' => Carbon::now()->toDateString()]);
                        }

                        DB::table('contractor_facility')
                            ->where('contractor_id', $contractor->id)
                            ->update(['deleted_at' => Carbon::now()->toDateString()]);
                    }

                }
            });

            Log::info("FIM");

            DB::commit();

        } catch (Exception $e) {
            DB::rollback();
            Log::error("Failed to migrate " . __FILE__);
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
        // Backup of deleted data here
        // https://contractorcomplianceio.atlassian.net/browse/DEV-1456

        Schema::table('contractor_position', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('position_role', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('contractor_facility', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
}
