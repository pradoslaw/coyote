<?php

namespace Coyote\Services\GeoIp;

use Illuminate\Contracts\Cache\Repository;

class Cache
{
    const TTL = 60 * 60 * 24 * 30; // 30d

    /**
     * @var GeoIp
     */
    protected $geoIp;

    /**
     * @var Repository
     */
    protected $cache;

    /**
     * @param GeoIp $geoIp
     * @param Repository $cache
     */
    public function __construct(GeoIp $geoIp, Repository $cache)
    {
        $this->geoIp = $geoIp;
        $this->cache = $cache;
    }

    /**
     * Geocode IP address and return location array
     *
     * @param string $ip
     * @return mixed
     */
    public function ip($ip)
    {
        return $this->cache->remember('ip:' . $ip, self::TTL, function () use ($ip) {
            return $this->geoIp->ip($ip);
        });
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([&$this->geoIp, $name], $arguments);
    }
}
