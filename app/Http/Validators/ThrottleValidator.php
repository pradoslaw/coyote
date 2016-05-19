<?php

namespace Coyote\Http\Validators;

use Coyote\Services\Declination\Declination;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use Illuminate\Contracts\Cache\Repository as Cache;

/**
 * Anti flood validator
 *
 * @package Coyote
 */
class ThrottleValidator
{
    const ANONYMOUS_DELAY = 60;
    const REGISTERED_DELAY = 15;
    const MIN_REPUTATION_POINT = 100;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @param Request $request
     * @param Cache $cache
     */
    public function __construct(Request $request, Cache $cache)
    {
        $this->request = $request;
        $this->cache = $cache;
    }

    /**
     * @param mixed $attribute
     * @param mixed $value
     * @param array $parameters
     * @param Validator $validator
     * @return bool
     */
    public function validateThrottle($attribute, $value, $parameters, $validator)
    {
        // $parameters[0] contains ID of saving post/microblog etc. If so user are not creating new item
        // so we should allow to proceed.
        if (!empty($parameters[0])) {
            return true;
        }

        // we need to calculate delay between request (it depends on user)
        $delay = $this->getFloodDelay();
        // build cache key name
        $key = $this->getCacheKeyName();

        $flood = $this->cache->get($key, 0);

        if (!$flood || time() - $flood > $delay) {
            // validation passes. it's good. now we can save current timestamp to the cache.
            // next time we can retrieve it and compare it to the current timestamp.
            if (!$validator->messages()->count()) {
                $this->cache->put($key, time(), $delay);
            }

            return true;
        }

        $validator->addReplacer('throttle', function ($message) use ($delay, $flood) {
            $seconds = $delay - round(time() - $flood);
            $declination = new Declination();

            return str_replace(':delay', $declination->format($seconds, ['sekundę', 'sekundy', 'sekund']), $message);
        });

        return false;
    }

    /**
     * @return string
     */
    protected function getCacheKeyName()
    {
        $key = $this->request->ip();

        if (!empty($this->request->user())) {
            $key .= $this->request->user()->id;
        } else {
            $key .= $this->request->header('User-Agent');
        }

        return 'flood:' . md5($key);
    }

    /**
     * @return int
     */
    protected function getFloodDelay()
    {
        $delay = self::ANONYMOUS_DELAY;

        if (!empty($this->request->user())) {
            $delay = self::REGISTERED_DELAY;

            if ($this->request->user()->reputation >= self::MIN_REPUTATION_POINT) {
                $delay = 0;
            }
        }

        return $delay;
    }
}
