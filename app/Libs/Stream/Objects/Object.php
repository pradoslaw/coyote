<?php

namespace Coyote\Stream\Objects;

use Coyote\Stream\Builder;

abstract class Object implements ObjectInterface
{
    use Builder;

    public function __construct(array $data = [])
    {
        foreach ($data as $field => $value) {
            $this->$field = $value;
        }
    }
}
