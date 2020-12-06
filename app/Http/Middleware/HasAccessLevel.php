<?php

namespace App\Http\Middleware;

use Closure;

class HasAccessLevel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, int $level = 4)
    {

        if ($request->user()->role->access_level < $level){

            return response([
                'message' => __('auth.access_level'),
            ], 402);
        }

        return $next($request);
    }
}
