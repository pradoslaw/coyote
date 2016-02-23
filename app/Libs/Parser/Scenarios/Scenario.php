<?php

namespace Coyote\Parser\Scenarios;

use Illuminate\Contracts\Cache\Repository as Cache;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Repositories\Contracts\WordRepositoryInterface as Word;

abstract class Scenario
{
    const CACHE_EXPIRATION = 60 * 24 * 30; // 30d

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Word
     */
    protected $word;

    /**
     * @var bool
     */
    protected $enableCache = true;

    private $crc32 = [];

    /**
     * @param Cache $cache
     * @param User $user
     * @param Word $word
     */
    public function __construct(Cache $cache, User $user, Word $word)
    {
        $this->cache = $cache;
        $this->user = $user;
        $this->word = $word;
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

    public function isSmiliesAllowed()
    {
        return auth()->check() && auth()->user()->allow_smilies;
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
    protected function isInCache($text)
    {
        return $this->enableCache && $this->cache->has($this->getCacheKey($text));
    }

    /**
     * @param $text
     * @return mixed
     */
    protected function getFromCache($text)
    {
        if (!$this->isInCache($text)) {
            return false;
        }

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