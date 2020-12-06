<?php

namespace App\Http\Controllers\Api;

use App\Events\LockEvent;
use App\Events\UnlockEvent;
use App\Http\Controllers\Controller;
use App\Models\HiringOrganization;
use App\Models\Lock;
use App\Models\Role;
use App\Traits\ErrorHandlingTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class LockController extends Controller
{
    use ErrorHandlingTrait;

    public function create(Request $request)
    {
//        Log::debug(__METHOD__, [
//            'request' => json_encode($request),
//        ]);

        try {

            $request_data = $request->validate([
                'entity_key' => 'required|string',
                'entity_id' => 'required|integer|min:2',
            ]);

            $isLocked = Lock::where('entity_key', $request_data['entity_key'])
                ->where('entity_id', $request_data['entity_id'])
                ->whereNull('deleted_at')
                ->sharedLock()
                ->first();

            if(!$isLocked) {
                $request_data['locker_role_id'] = $request->user()->role->id;
                $request_data['ends_at'] = Carbon::now()->addMinutes(config('api.lock_ttl'));

                $return = Lock::create($request_data);

                $locked_item = [
                    'entity_id' => $return->entity_id,
                    'entity_key' => $return->entity_key,
                    'hiring_organization_id' => $request->user()->role->company->id,
                    'user' => $request->user(),
                ];

                Redis::throttle(get_class($this))->allow(15)->every(60)->then(function () use ($locked_item) {
                    broadcast(new LockEvent($locked_item))->toOthers();
                }, function () {
                    return $this->release(2);
                });

                return response($locked_item, 200);

            } else {
                $role = Role::find($isLocked->locker_role_id);

                $locked_item = [
                    'entity_id' => $isLocked->entity_id,
                    'entity_key' =>$isLocked->entity_key,
                    'hiring_organization_id' => $role->company->id,
                    'user' => $role->user,
                ];

                return response($locked_item, 200);

            }

        } catch (Exception $e) {
            return $this->errorResponse($e, 400);
        }
    }

    public function read(Request $request, HiringOrganization $hiring_organization)
    {

        Log::debug(__METHOD__, [
            'request' => $request,
        ]);

        $request_data = $request->validate([
            'entity_key' => 'required|string'
        ]);

        $results = DB::table('locks')
            ->join('roles', 'roles.id', '=', 'locks.locker_role_id' )
            ->where('roles.entity_key', '=', 'hiring_organization')
            ->where('roles.entity_id', '=', $hiring_organization->id)
            ->where('locks.entity_key', '=', $request_data['entity_key'])
            ->where("ends_at", ">", Carbon::now())
            ->whereNull('locks.deleted_at')
            ->select('locks.entity_id', 'locker_role_id')
            ->distinct()
            ->get();

        $locked = [];
        foreach ($results as $result){
            $role = Role::find($result->locker_role_id);

            $locked[] = [
                'entity_key' => $request_data['entity_key'],
                'entity_id' => $result->entity_id,
                'hiring_organization_id' => $hiring_organization->id,
                'user' => $role->user,
            ];
        }

        return response($locked);
    }

    public function extend_expiration_time(Request $request)
    {
//        Log::debug(__METHOD__, [
//            'request' => $request,
//        ]);

        try {

            $request_data = $request->validate([
                'entity_key' => 'required|string',
                'entity_id' => 'required|integer|min:2',
            ]);

            $lock = Lock::where('entity_id', $request_data['entity_id'])
                ->where('entity_key', $request_data['entity_key'])
                ->where('locker_role_id', $request->user()->role->id)
                ->first();

            $lock->ends_at = Carbon::now()->addMinutes(config('api.lock_ttl'));

            $lock->save();

            $lock->refresh();

            return $this->read($request, HiringOrganization::find($request->user()->role->company->id));

        } catch (Exception $e) {
            return $this->errorResponse($e, 400);
        }
    }

    public function delete(Request $request)
    {
//        Log::debug(__METHOD__, [
//            'request' => json_encode($request),
//        ]);

        try {
            $request_data = $request->validate([
                'entity_key' => 'required|string',
                'entity_id' => 'required|integer|min:2',
            ]);

            $deleted = Lock::where('entity_key', $request_data['entity_key'])
                ->where('entity_id', $request_data['entity_id'])
                ->where('locker_role_id', $request->user()->role->id)
                ->delete();

            if($deleted){
                $deleted_item = [
                    'entity_id' => $request_data['entity_id'],
                    'entity_key' => $request_data['entity_key'],
                    'hiring_organization_id' => $request->user()->role->company->id,
                    'user' => $request->user()
                ];

                Redis::throttle(get_class($this))->allow(15)->every(60)->then(function () use ($deleted_item) {
                    //Since it doesnt broadcast to myself, sometimes the screen get locked with my own locker, trying to solve it broadcasting to everyone
                    broadcast(new UnlockEvent($deleted_item));
//                    broadcast(new UnlockEvent($deleted_item))->toOthers();
                }, function () {
                    return $this->release(2);
                });

            }

            return $this->read($request, HiringOrganization::find($request->user()->role->company->id));

        } catch (Exception $e) {
            return $this->errorResponse($e, 400);
        }
    }
}
