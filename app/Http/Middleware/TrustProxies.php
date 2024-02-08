<?php
namespace Coyote\Http\Middleware;

use Illuminate\Http\Request;

class TrustProxies extends \Illuminate\Http\Middleware\TrustProxies
{
    protected $proxies = '*';

    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
