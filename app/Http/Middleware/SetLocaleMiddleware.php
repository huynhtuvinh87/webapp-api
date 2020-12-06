<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocaleMiddleware
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

        if ($request->header('Accept-Language') && in_array($request->header('Accept-Language'), config('app.available_locales'))) {

            App::setLocale($request->header('Accept-Language'));
        }

        return $next($request);
    }
}
