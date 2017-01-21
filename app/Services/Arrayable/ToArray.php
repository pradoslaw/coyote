<?php

namespace Coyote\Services\Arrayable;

trait ToArray
{
    /**
     * Converts object to array
     *
     * @return array
     */
    public function toArray()
    {
        $reflect = new \ReflectionObject($this);
        $props   = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);

        $result = [];

        foreach ($props as $prop) {
            $result[$prop->getName()] = $prop->getValue($this);
        }

        return $result;
    }
}
