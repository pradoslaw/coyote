<?php

namespace Coyote\Services\Stream\Activities;

use Coyote\Services\Arrayable\ToArray;
use Coyote\Services\Stream\Objects\ObjectInterface;

/**
 * Class Activity
 */
abstract class Activity implements ObjectInterface
{
    use ToArray{
        toArray as parentToArray;
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
    public $verb;

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
     * @param ObjectInterface|null $object
     * @param ObjectInterface|null $target
     */
    public function __construct(ObjectInterface $actor, ObjectInterface $object = null, ObjectInterface $target = null)
    {
        $this->actor = $actor;
        $this->object = $object;
        $this->target = $target;
        $this->ip = request()->ip();

        $this->browser = request()->browser();

        if (empty($this->verb)) {
            $this->verb = strtolower(class_basename($this));
        }
    }

    /**
     * @param ObjectInterface $object
     * @return $this
     */
    public function setObject(ObjectInterface $object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @param ObjectInterface $target
     * @return $this
     */
    public function setTarget(ObjectInterface $target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = $this->parentToArray();

        foreach (['actor', 'object', 'target'] as $field) {
            if (is_object($this->{$field}) && $this->{$field} instanceof ObjectInterface) {
                $array[$field] = $this->{$field}->toArray();
            }
        }

        return $array;
    }
}
