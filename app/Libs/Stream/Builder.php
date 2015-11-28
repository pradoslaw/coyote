<?php

namespace Coyote\Stream;

/**
 * Class Builder
 * @package Coyote\Stream
 */
trait Builder
{
    public function build()
    {
        return $this->buildObject($this);
    }

    protected function buildObject($object)
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
