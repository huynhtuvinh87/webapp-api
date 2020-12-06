<?php

namespace App\Http\Middleware;

use Closure;
use Log;
use Exception;
use Carbon\Carbon;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws Exception
     */
    public function handle($request, Closure $next)
    {
        Log::debug(__METHOD__, ['request' => $request]);

        $now = Carbon::today()->endOfDay();
        $invalidSubscription = false;

        if ($request->user()->role->entity_key == 'contractor') {

            $subscription = $request->user()->role->company->localSubscription;

            if (!isset($subscription)) {
                Log::info("Contractor " . $request->user()->role->entity_id . " doesn't not have subscription entry in table subscription, will return 402");
                $invalidSubscription = true;
            }

            if(isset($subscription->ends_at)){
                if (Carbon::createFromFormat('Y-m-d H:i:s', $subscription->ends_at)->lt($now)) {
                    Log::info("Contractor " . $request->user()->role->entity_id . "  has an expired subscription table subscription, will return 402");
                    $invalidSubscription = true;
                }
            }

            if ($invalidSubscription) {
                return response([
                    'message' => __('auth.requires_payment'),
                ], 402);
            }


        } else {
            throw new Exception('Contractor Middleware applied to NOT Contractor roles');
        }

        return $next($request);
    }
}
