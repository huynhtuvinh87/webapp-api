<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Log;
use App\Traits\CacheTrait;

class ResponseCacheMiddleware
{
    use CacheTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $tags = $this->buildTagsFromRequest($request);
        $key = $this->buildKeyFromRequest($request);

        return Cache::tags($tags)
            ->remember($key, config('cache.time'), function () use ($request, $next) {
                $response = $next($request);
                return $response;
            });

    }

}
