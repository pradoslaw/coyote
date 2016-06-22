<?php

namespace Coyote\Services\FormBuilder\Fields;

use Coyote\Services\FormBuilder\Form;

abstract class ParentType extends Field
{
    /**
     * @var Field[]
     */
    protected $children = [];

    /**
     * @var Field[]
     */
    protected $childAttr = [];

    /**
     * ChildForm constructor.
     * @param $name
     * @param $type
     * @param Form $parent
     * @param array $options
     */
    public function __construct($name, $type, Form $parent, array $options)
    {
        parent::__construct($name, $type, $parent, $options);

        if (empty($this->children)) {
            $this->createChildren();
        }
    }

    /**
     * @return Field[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Get a child
     *
     * @return mixed
     */
    public function getChild($key)
    {
        return array_get($this->children, $key);
    }

    /**
     * @return array
     */
    public function getChildAttr()
    {
        return $this->childAttr;
    }

    /**
     * @param array $childAttr
     * @return $this
     */
    public function setChildAttr($childAttr)
    {
        $this->childAttr = $childAttr;

        return $this;
    }

    /**
     * Create children elements
     */
    abstract protected function createChildren();
}
