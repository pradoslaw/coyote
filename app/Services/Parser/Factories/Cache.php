<?php

namespace Coyote\Services\Parser\Factories;

use Illuminate\Contracts\Cache\Repository;

class Cache
{
    const CACHE_TTL = 60 * 24 * 30; // 30d

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
     * @param mixed $id
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

    /**
     * @param $text
     */
    public function put(&$text)
    {
        $this->repository->put($this->cacheKey($text), $text, self::CACHE_TTL);
    }

    /**
     * @param string $text
     * @return bool
     */
    public function has(&$text)
    {
        return $this->enable && $this->repository->has($this->cacheKey($text));
    }

    /**
     * @param string $text
     * @return mixed
     */
    public function get(&$text)
    {
        return $this->repository->get($this->cacheKey($text));
    }

    /**
     * @param string $text
     */
    public function forget(&$text)
    {
        $this->repository->forget($this->cacheKey($text));
    }

    /**
     * @param $text
     * @return string
     */
    protected function cacheKey(&$text)
    {
        return 'text:' . md5($text) . $this->id;
    }
}
