<?php


namespace Coyote\Services\Firewall;

use Coyote\Fingerprint; // <-- nie usuwac tej linii
use Coyote\Repositories\Contracts\FirewallRepositoryInterface;
use Illuminate\Cache\Repository as Cache;
use Coyote\Firewall;
use Illuminate\Http\Request;

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
     * @var Request
     */
    private $request;

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
     * @param Request $request
     * @return \Coyote\Firewall|null
     */
    public function find(Request $request)
    {
        $this->request = $request;

        foreach ($this->getRules() as $rule) {
            if ($this->checkIpRule($rule['ip'])
                || $this->checkUserRule($rule['user_id'])
                    || $this->checkFingerprintRule($rule['fingerprint'] ?? null)) {
                return $this->make($rule);
            }
        }

        return null;
    }

    /**
     * @param string|null $ip
     * @return bool
     */
    private function checkIpRule($ip): bool
    {
        if (empty($ip)) {
            return false;
        }

        return preg_match('/^' . str_replace('\*', '\d+', preg_quote($ip)) . '$/', $this->request->ip());
    }

    /**
     * @param int|null $userId
     * @return bool
     */
    private function checkUserRule($userId): bool
    {
        if (empty($userId) || empty($this->request->user())) {
            return false;
        }

        return $this->request->user()->id == $userId;
    }

    /**
     * @param $fingerprint
     * @return bool
     */
    private function checkFingerprintRule($fingerprint)
    {
        if (empty($fingerprint)) {
            return false;
        }

        return $fingerprint === $this->getClientFingerprint();
    }

    /**
     * @return mixed
     */
    private function getClientFingerprint()
    {
        static $fingerprint;

        if (!empty($fingerprint)) {
            return $fingerprint;
        }

        if (class_exists(Fingerprint::class)) {
            $fingerprint = Fingerprint::get();
        }

        return $fingerprint;
    }

    /**
     * @return array
     */
    private function getRules()
    {
        return unserialize(
            $this->cache->rememberForever(self::CACHE_KEY, function () {
                return serialize($this->repository->all(['id', 'user_id', 'ip', 'fingerprint'])->toArray());
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
        return Firewall::find($firewall['id']);
    }
}
