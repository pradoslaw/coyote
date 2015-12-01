<?php

namespace Coyote\Stream;

/**
 * Class Builder
 * @package Coyote\Stream
 */
trait Builder
{
    /**
     * @return array
     */
    public function build()
    {
        return $this->toArray($this);
    }

    /**
     * Converts object to array
     *
     * @param mixed $object
     * @return array
     */
    protected function toArray($object)
    {
        $reflect = new \ReflectionObject($object);
        $props   = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);

        $result = [];

        foreach ($props as $prop) {
            $result[$prop->getName()] = $prop->getValue($object);
        }

        return $result;
    }
}
