<?php

namespace Coyote;

use Coyote\Services\Arrayable\ToArray;
use Illuminate\Contracts\Support\Arrayable;

class Session implements \ArrayAccess, Arrayable
{
    use ToArray {
        toArray as parentToArray;
    }

    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $group;

    /**
     * @var string
     */
    public $guestId;

    /**
     * @var int|null
     */
    public $userId;

    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $ip;

    /**
     * @var int
     */
    public $createdAt;

    /**
     * @var int
     */
    public $updatedAt;

    /**
     * @var string
     */
    public $browser;

    /**
     * @var string
     */
    public $robot;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $prop = camel_case($key);

            if (property_exists($this, $prop)) {
                $this->{$prop} = $value;
            }
        }
    }

    /**
     * @param int $lifetime
     * @return bool
     */
    public function expired(int $lifetime): bool
    {
        return $this->updatedAt < time() - $lifetime;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return property_exists($this, camel_case($offset));
    }

    public function offsetGet($offset): mixed
    {
        return $this->{camel_case($offset)};
    }

    public function offsetSet($offset, $value): void
    {
        $this->{camel_case($offset)} = $value;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetUnset($offset)
    {
        return true;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [];

        foreach ($this->parentToArray() as $key => $value) {
            $result[snake_case($key)] = $value;
        }

        return $result;
    }
}
