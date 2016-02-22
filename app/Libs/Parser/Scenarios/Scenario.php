<?php

namespace Coyote\Parser\Scenarios;

use Illuminate\Contracts\Cache\Repository as Cache;

abstract class Scenario
{
    const CACHE_EXPIRATION = 60 * 24 * 30; // 30d

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var bool
     */
    protected $enableCache = true;

    /**
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setEnableCache($flag)
    {
        $this->enableCache = (bool) $flag;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCacheEnabled()
    {
        return $this->enableCache;
    }

    /**
     * @param $text
     * @return string
     */
    protected function getCacheKey($text)
    {
        return 'text:' . class_basename($this) . hash('crc32b', $text);
    }

    /**
     * @param $text
     * @return bool
     */
    protected function inCache($text)
    {
        return $this->enableCache && $this->cache->has($this->getCacheKey($text));
    }

    /**
     * @param $text
     * @return mixed
     */
    protected function fromCache($text)
    {
        return $this->cache->get($this->getCacheKey($text));
    }

    /**
     * Parse text and store it in cache
     *
     * @param $text
     * @param \Closure $closure
     * @return mixed
     */
    public function cache($text, \Closure $closure)
    {
        $parser = $closure();

        $key = $this->getCacheKey($text);
        $text = $parser->parse($text);

        if ($this->enableCache) {
            $this->cache->put($key, $text, self::CACHE_EXPIRATION);
        }

        $parser->detach();
        return $text;
    }
}