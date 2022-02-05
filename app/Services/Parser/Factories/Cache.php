<?php

namespace Coyote\Services\Parser\Factories;

use Illuminate\Contracts\Cache\Repository;

class Cache
{
    const CACHE_TTL = 60 * 60 * 24 * 14; // 14d

    /**
     * @var bool
     */
    protected $enable = false;

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

    /**
     * @param string $key
     * @param string $text
     */
    public function put($key, &$text)
    {
        $this->repository->put($key, $text, self::CACHE_TTL);
    }

    /**
     * @param string $text
     * @return bool
     */
    public function has(&$text)
    {
        return $this->enable && $this->repository->has($this->key($text));
    }

    /**
     * @param string $text
     * @return mixed
     */
    public function get(&$text)
    {
        return $this->repository->get($this->key($text));
    }

    /**
     * @param string $text
     */
    public function forget($text)
    {
        $this->repository->forget($this->key($text));
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
