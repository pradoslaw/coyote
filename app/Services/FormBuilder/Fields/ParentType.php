<?php

namespace Coyote\Services\FormBuilder\Fields;

abstract class ParentType extends Field
{
    /**
     * @var Field[]
     */
    protected $children = [];

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
     * Create children elements
     */
    abstract protected function createChildren();
}
