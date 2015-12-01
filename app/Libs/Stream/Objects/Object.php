<?php

namespace Coyote\Stream\Objects;

use Coyote\Stream\Builder;

/**
 * Class Object
 * @package Coyote\Stream\Objects
 */
abstract class Object implements ObjectInterface
{
    use Builder;

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $displayName;

    /**
     * @var string
     */
    public $objectType;

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $field => $value) {
            $this->$field = $value;
        }

        if (empty($this->objectType)) {
            $this->objectType = strtolower(class_basename($this));
        }
    }
}
