<?php

namespace App\Console\Commands;

use App\Models\Contractor;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearOldInvites extends Command
{
    /**
     * The name and signature of the console command.
     * TODO FIX THIS
     * @var string
     */
    protected $signature = 'invites:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear invites older than 3 months (consider making this configurable)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $rel = DB::table('contractor_hiring_organization')
            ->whereRaw('invited_at <= now() - interval 3 month')
            ->where('accepted', 0)
            ->get();

        if ($rel->count()){
            $rel = $rel->toArray();
            $this->info($rel[0]->id);
            foreach($rel as $re){

                dispatch(function() use ($re){

                    $contractor = Contractor::find($re->contractor_id);
                    $role = Role::where('entity_id', $contractor->id)->where('entity_id', 'contractor')->first();
                    $user = User::find($role->user_id);

                    //$this->info("Deleting $user->email, $contractor->name");

                    $contractor->delete();
                    $role->delete();
                    $user->delete();

                });

            }
        }
        else {
            $this->info("Nothing to delete");
        }
    }
}
