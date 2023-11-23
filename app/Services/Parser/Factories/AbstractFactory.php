<?php

namespace Coyote\Services\Parser\Factories;

use Illuminate\Http\Request;
use Illuminate\Container\Container as App;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Auth\Factory as Auth;

abstract class AbstractFactory
{
    /**
     * @var Cache
     */
    public $cache;

    /**
     * @var App
     */
    protected $app;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Auth
     */
    protected $auth;

    /**
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;

        $this->cache = new Cache($app[Repository::class]);
        $this->cache->setId(class_basename($this));

        $this->request = $app[Request::class];
        $this->auth = $app[Auth::class];
    }

    /**
     * @param string $text
     * @return string
     */
    abstract public function parse(string $text) : string;

    public function isSmiliesAllowed(): bool
    {
        return $this->auth->check() && $this->auth->user()->allow_smilies;
    }

    /**
     * Parse text and store it in cache
     */
    public function cache(string $text, \Closure $closure): string
    {
        $key = $this->cache->key($text);

        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        /** @var \Coyote\Services\Parser\CompositeParser $parser */
        $parser = $closure();
        $text = $parser->parse($text);

        if ($this->cache->isEnabled()) {
            $this->cache->put($key, $text);
        }

        $parser->detach();

        return $text;
    }
}
