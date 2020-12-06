<?php

namespace App\Http\Middleware;

use Closure;

class IsHiringOrganizationAdmin
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

        if (($request->user()->role->role !== "admin" && $request->user()->role->role !== "owner") || $request->user()->role->entity_key !== "hiring_organization") {
            return response("Not Authorized", 403);
        }

        return $next($request);
    }
}
