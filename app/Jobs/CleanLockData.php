<?php

namespace App\Jobs;

use App\Events\UnlockEvent;
use App\Models\Lock;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CleanLockData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->queue = 'low';
        $this->connection = 'database';
    }

    public function handle()
    {

        // items to be deleted, must use withTrashed to bring softdeleted ones
        $results = Lock::where('ends_at', '<', Carbon::now())
            ->orWhereNotNull('deleted_at')
            ->withTrashed()
            ->get();

        //delete and trigger event to broadcast the changes into front end
        if (isset($results) && !empty($results)) {
            foreach ($results as $result) {
                $lock = Lock::where('id', $result->id)->withTrashed()->first(); // must use withTrashed to bring softdeleted ones

                if ($lock) {
                    // create obj to be broadcasted
                    $role = Role::find($lock->locker_role_id);

                    $deleted_item = [
                        'entity_id' => $lock['entity_id'],
                        'entity_key' => $lock['entity_key'],
                        'hiring_organization_id' => $role->company->id,
                        'user' => $role->user->email
                    ];

                    // force delete to keep the table clean
                    $lock->forceDelete();

                    // broadcast the changes
                    Redis::throttle(get_class($this))->allow(15)->every(60)->then(function () use ($deleted_item) {
                        broadcast(new UnlockEvent($deleted_item));
                    }, function () {
                        return $this->release(2);
                    });
                }

            }
        }
    }
}
