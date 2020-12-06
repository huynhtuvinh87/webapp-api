<?php

namespace App\Http\Middleware;

use Fideloper\Proxy\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array
     */
    protected $proxies = [
        // DEVELOPMENT SERVERS
        '3.135.71.39', // Development LB
        '10.0.2.67', // Development LB Internal IP

        '18.223.89.7', // Development_1 Server
        '10.0.1.183', // Development_1 Server - Internal IP

        // PRODUCTION SERVERS
        '3.132.245.64', // Production LB
        '10.0.2.179', // Production LB Internal

        '18.222.118.74', // Production 1
        '10.0.1.15', // Production 1 Internal
    ];

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_ALL;
}
