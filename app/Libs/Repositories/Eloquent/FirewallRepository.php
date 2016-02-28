<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\FirewallRepositoryInterface;
use Cache;

class FirewallRepository extends Repository implements FirewallRepositoryInterface
{
    const CACHE_KEY = 'firewall';

    /**
     * @return \Coyote\Firewall
     */
    public function model()
    {
        return 'Coyote\Firewall';
    }

    private function load()
    {
        if (Cache::has(self::CACHE_KEY)) {
            $result = json_decode(Cache::get(self::CACHE_KEY), true);
        } else {
            $result = $this->all()->toArray();
            Cache::forever(self::CACHE_KEY, json_encode($result));
        }

        return $result;
    }

    /**
     * @param $userId
     * @param $ip
     * @return bool
     */
    public function filter($userId, $ip)
    {
        $result = [];

        foreach ($this->load() as $row) {
            if ($row['ip']) {//dd(('/^' . str_replace('\*', '\d+', preg_quote($row['ip'])) . '$/'));
                if (preg_match('/^' . str_replace('\*', '\d+', preg_quote($row['ip'])) . '$/', $ip)) {
                    $result = $row;
                    break;
                }
            }

            if ($row['user_id'] && $userId == $row['user_id']) {
                $result = $row;
                break;
            }
        }

        return $result;
    }
}
