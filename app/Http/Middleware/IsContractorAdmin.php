<?php

namespace App\Http\Middleware;

use Closure;

class IsContractorAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (($request->user()->role->role !== "admin" && $request->user()->role->role !== "owner") || $request->user()->role->entity_key !== "contractor") {
            return response("Not Authorized", 403);
        }

        return $next($request);
    }
}
