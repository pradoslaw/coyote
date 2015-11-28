<?php

namespace Coyote\Stream\Activities;

use Coyote\Stream\Builder;
use Coyote\Stream\Objects\ObjectInterface;

/**
 * Class Activity
 * @package Coyote\Stream\Activities
 */
abstract class Activity implements ObjectInterface
{
    use Builder {
        buildObject as buildChildObject;
    }

    /**
     * @var ObjectInterface
     */
    protected $actor;

    /**
     * @var ObjectInterface
     */
    protected $object;

    /**
     * @var ObjectInterface|null
     */
    protected $target;

    /**
     * @var string
     */
    public $ip;

    /**
     * @var string
     */
    public $browser;

    /**
     * @param ObjectInterface $actor
     * @param ObjectInterface $object
     * @param ObjectInterface|null $target
     */
    public function __construct(ObjectInterface $actor, ObjectInterface $object, ObjectInterface $target = null)
    {
        $this->actor = $actor;
        $this->object = $object;
        $this->target = $target;
        $this->ip = request()->ip();

        if (method_exists(request(), 'browser')) {
            $this->browser = request()->browser();
        }
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
