<?php
namespace Coyote\Services\Parser\Factories;

use Coyote\Services\Parser\CompositeParser;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth;
use Illuminate\Contracts\Cache\Repository;

abstract class AbstractFactory
{
    public Cache $cache;
    protected Container $container;
    protected Auth\Factory $auth;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->cache = new Cache($container[Repository::class]);
        $this->cache->setId(class_basename($this));
        $this->auth = $container[Auth\Factory::class];
    }

    abstract public function parse(string $text): string;

    public function smiliesAllowed(): bool
    {
        return $this->auth->check() && $this->auth->user()->allow_smilies;
    }

    public function videoAllowed(): bool
    {
        if ($this->auth->check()) {
            $user = $this->auth->user();
            return $user->can('forum-emphasis');
        }
        return false;
    }

    public function parseAndCache(string $text, callable $closure): string
    {
        $key = $this->cache->key($text);

//        if ($this->cache->has($key)) {
//            return $this->cache->get($key);
//        }

        /** @var CompositeParser $parser */
        $parser = $closure();
        $text = $parser->parse($text);

        if ($this->cache->isEnabled()) {
            $this->cache->put($key, $text);
        }

        $parser->removeAll();

        return $text;
    }
}
