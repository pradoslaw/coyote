<?php

namespace Coyote\Services\Parser\Factories;

use Illuminate\Contracts\Cache\Repository;

class Cache
{
    const CACHE_TTL = 60 * 60 * 24 * 14; // 14d

    /**
     * @var bool
     */
    protected $enable = true;

    /**
     * @var
     */
    protected $id;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Additional cache id parameter makes content unique. This is useful if we have like two identical comments
     * but we want to parse and cache them differently.
     *
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setEnable($flag)
    {
        $this->enable = $flag;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enable;
    }

    public function put(string $key, string &$text): void
    {
        $this->repository->put($key, $text, self::CACHE_TTL);
    }

    public function has(string $key): bool
    {
        return $this->enable && $this->repository->has($key);
    }

    public function get(string $key): string
    {
        return $this->repository->get($key);
    }

    public function forget(string $key): void
    {
        $this->repository->forget($key);
    }

    /**
     * @param $text
     * @return string
     */
    public function key(&$text)
    {
        return 'text:' . md5($text) . $this->id;
    }
}
