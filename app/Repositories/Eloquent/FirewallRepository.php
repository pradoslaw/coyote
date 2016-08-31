<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\FirewallRepositoryInterface;
use Coyote\Firewall;

class FirewallRepository extends Repository implements FirewallRepositoryInterface
{
    const CACHE_KEY = 'firewall';

    /**
     * @return string
     */
    public function model()
    {
        return Firewall::class;
    }

    /**
     * @param $userId
     * @param $ip
     * @return \Coyote\Firewall|null
     */
    public function filter($userId, $ip)
    {
        $model = null;

        foreach ($this->getRules() as $rule) {
            if ($rule['ip']) {
                if (preg_match('/^' . str_replace('\*', '\d+', preg_quote($rule['ip'])) . '$/', $ip)) {
                    $model = $this->toModel($rule);
                    break;
                }
            }

            if ($rule['user_id'] && $userId == $rule['user_id']) {
                $model = $this->toModel($rule);
                break;
            }
        }

        return $model;
    }

    /**
     * Purge expired firewall entries
     */
    public function purge()
    {
        $this->model->whereNotNull('expire_at')->where('expire_at', '<=', $this->raw('NOW()'))->delete();
    }

    /**
     * @return array
     */
    private function getRules()
    {
        if ($this->app['cache']->has(self::CACHE_KEY)) {
            $result = unserialize($this->app['cache']->get(self::CACHE_KEY));
        } else {
            $result = $this->all()->toArray();
            $this->app['cache']->forever(self::CACHE_KEY, serialize($result));
        }

        return $result;
    }

    /**
     * @param array $firewall
     * @return Firewall
     */
    private function toModel(array $firewall)
    {
        return new Firewall($firewall);
    }
}
