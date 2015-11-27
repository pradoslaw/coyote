<?php

namespace Coyote\Stream\Activities;

use Coyote\Stream\Builder;
use Coyote\Stream\Objects\ObjectInterface;

abstract class Activity implements ObjectInterface
{
    use Builder {
        buildObject as buildChildObject;
    }

    protected $actor;
    protected $object;
    protected $target;
    public $ip;

    public function __construct(ObjectInterface $actor, ObjectInterface $object, ObjectInterface $target = null)
    {
        $this->actor = $actor;
        $this->object = $object;
        $this->target = $target;
        $this->ip = request()->getClientIp();

    }

    public function build()
    {
        $result = $this->buildObject($this);

        foreach (['actor', 'object', 'target'] as $field) {
            if (is_object($this->$field)) {
                $result[$field] = $this->buildChildObject($this->$field);
            }
        }

        return $result;
    }
}
