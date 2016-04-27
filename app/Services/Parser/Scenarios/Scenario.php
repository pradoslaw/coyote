<?php

namespace Coyote\Services\Parser\Scenarios;

use Coyote\Repositories\Contracts\PageRepositoryInterface as Page;
use Illuminate\Contracts\Cache\Repository as Cache;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Repositories\Contracts\WordRepositoryInterface as Word;
use Illuminate\Http\Request;
use Illuminate\Container\Container as App;

abstract class Scenario
{
    const CACHE_EXPIRATION = 60 * 24 * 30; // 30d

    protected $app;

    /**
     * @var Request
     */
    protected $request;

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
     * @var Page
     */
    protected $page;

    /**
     * @var bool
     */
    protected $enableCache = true;

    /**
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->cache = $app['Illuminate\Contracts\Cache\Repository'];
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
     * @return bool
     */
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
        return 'text:' . class_basename($this) . md5($text);
    }

    /**
     * @param string $text
     * @return bool
     */
    protected function isInCache($text)
    {
        return $this->enableCache && $this->cache->has($this->getCacheKey($text));
    }

    /**
     * @param string $text
     * @return mixed
     */
    protected function getFromCache($text)
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
