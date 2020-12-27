<?php

namespace Coyote\Services\Media;

trait SerializeClass
{
    public function __sleep()
    {
        $this->unsetRelation('assets');

        foreach ($this->attributes as $key => $value) {
            if ($value instanceof File) {
                $this->attributes[$key] = $this->attributes[$key]->getFilename();
            }
        }

        $properties = (new \ReflectionClass($this))->getProperties();

        $result = [];

        foreach ($properties as $property) {
            if (!$property->isStatic()) {
                $result[] = $property->getName();
            }
        }

        return $result;
    }
}
