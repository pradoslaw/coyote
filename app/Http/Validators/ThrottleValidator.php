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
     * @var int
     */
    private $lockoutTime;

    /**
     * @var string
     */
    private $key;

    /**
     * @param Request $request
     * @param Cache $cache
     */
    public function __construct(Request $request, Cache $cache)
    {
        $this->request = $request;
        $this->cache = $cache;

        // we need to calculate delay between request (it depends on user)
        $this->lockoutTime = $this->lockoutTime();
        // build cache key name
        $this->key = $this->getThrottleKey();
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

        if (!$this->tooManyAttempts()) {
            // validation passes. it's good. now we can save current timestamp to the cache.
            // next time we can retrieve it and compare it to the current timestamp.
            if (!$validator->messages()->count()) {
                $this->hit();
            }

            return true;
        }

        $validator->addReplacer('throttle', function ($message) {
            $declination = new Declination();

            return str_replace(
                ':delay',
                $declination->format($this->availableIn(), ['sekundę', 'sekundy', 'sekund']),
                $message
            );
        });

        return false;
    }

    /**
     * @return bool
     */
    protected function tooManyAttempts()
    {
        $timestamp = $this->cache->get($this->key, 0);

        return !($timestamp === 0 || time() - $timestamp > $this->lockoutTime);
    }

    /**
     * @void
     */
    protected function hit()
    {
        $this->cache->put($this->key, time(), $this->lockoutTime);
    }

    /**
     * @return int
     */
    protected function availableIn()
    {
        return $this->lockoutTime - round(time() - $this->cache->get($this->key));
    }

    /**
     * @return string
     */
    protected function getThrottleKey()
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
    protected function lockoutTime()
    {
        $lockoutTime = self::ANONYMOUS_DELAY;

        if (!empty($this->request->user())) {
            $lockoutTime = self::REGISTERED_DELAY;

            if ($this->request->user()->reputation >= self::MIN_REPUTATION_POINT) {
                $lockoutTime = 0;
            }
        }

        return $lockoutTime;
    }
}
