<?php

namespace App\Http\Middleware;

use Closure;

class GlobalAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->user()->global_admin !== 1){
            return response('Not an admin', 404);
        }

        return $next($request);
    }
}
