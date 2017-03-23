<?php


namespace Coyote\Services\Firewall;

use Coyote\Repositories\Contracts\FirewallRepositoryInterface;
use Illuminate\Cache\Repository as Cache;
use Coyote\Firewall;

class Rules
{
    const CACHE_KEY = 'firewall';

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var FirewallRepositoryInterface
     */
    private $repository;

    /**
     * @param Cache $cache
     * @param FirewallRepositoryInterface $repository
     */
    public function __construct(Cache $cache, FirewallRepositoryInterface $repository)
    {
        $this->cache = $cache;
        $this->repository = $repository;
    }

    /**
     * Find firewall rule based on user id and/or IP.
     *
     * @param $userId
     * @param $ip
     * @return \Coyote\Firewall|null
     */
    public function find($userId, $ip)
    {
        foreach ($this->getRules() as $rule) {
            if ($this->checkIpRule($rule['ip'], $ip) || $this->checkUserRule($rule['user_id'], $userId)) {
                return $this->make($rule);
            }
        }

        return null;
    }

    /**
     * @param $ruleIp
     * @param $clientIp
     * @return bool
     */
    private function checkIpRule($ruleIp, $clientIp): bool
    {
        if (empty($ruleIp)) {
            return false;
        }

        return preg_match('/^' . str_replace('\*', '\d+', preg_quote($ruleIp)) . '$/', $clientIp);
    }

    /**
     * @param $ruleUserId
     * @param $clientUserId
     * @return bool
     */
    private function checkUserRule($ruleUserId, $clientUserId): bool
    {
        return !empty($ruleUserId) && $clientUserId == $ruleUserId;
    }

    /**
     * @return array
     */
    private function getRules()
    {
        return unserialize(
            $this->cache->rememberForever(self::CACHE_KEY, function () {
                return serialize($this->repository->all()->toArray());
            })
        );
    }

    /**
     * Make model from array.
     *
     * @param array $firewall
     * @return Firewall
     */
    private function make(array $firewall): Firewall
    {
        return (new Firewall)->forceFill($firewall); // forceFill to set ID
    }
}
