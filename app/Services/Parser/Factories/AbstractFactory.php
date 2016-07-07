<?php

namespace Coyote\Services\Parser\Factories;

use Illuminate\Http\Request;
use Illuminate\Container\Container as App;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Auth\Factory as Auth;

abstract class AbstractFactory
{
    const CACHE_EXPIRATION = 60 * 24 * 30; // 30d

    /**
     * @var App
     */
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
     * @var Auth
     */
    protected $auth;

    /**
     * @var bool
     */
    protected $enableCache = true;

    /**
     * @var string
     */
    private $cacheId;

    /**
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;

        $this->cache = $app[Cache::class];
        $this->request = $app[Request::class];
        $this->auth = $app[Auth::class];
    }

    /**
     * @param string $text
     * @return string
     */
    abstract public function parse(string $text) : string;

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
        return $this->auth->check() && $this->auth->user()->allow_smilies;
    }

    /**
     * @param string $text
     * @return mixed
     */
    public function getFromCache($text)
    {
        return $this->cache->get($this->cacheKey($text));
    }

    /**
     * @param string $text
     */
    public function purgeFromCache($text)
    {
        $this->cache->forget($this->cacheKey($text));
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
        /** @var \Coyote\Services\Parser\Container $parser */
        $parser = $closure();

        $key = $this->cacheKey($text);
        $text = $parser->parse($text);

        if ($this->enableCache) {
            $this->cache->put($key, $text, self::CACHE_EXPIRATION);
        }

        $parser->detach();
        return $text;
    }

    /**
     * Additional cache id parameter makes content unique. This is useful if we have like two identical comments
     * but we want to parse and cache them differently.
     *
     * @param string $cacheId
     * @return $this
     */
    public function setCacheId($cacheId)
    {
        $this->cacheId = $cacheId;

        return $this;
    }

    /**
     * @param $text
     * @return string
     */
    protected function cacheKey($text)
    {
        return 'text:' . class_basename($this) . md5($text) . $this->cacheId;
    }

    /**
     * @param string $text
     * @return bool
     */
    protected function isInCache($text)
    {
        return $this->enableCache && $this->cache->has($this->cacheKey($text));
    }
}
