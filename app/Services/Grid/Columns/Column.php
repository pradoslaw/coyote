<?php

namespace Coyote\Services\Grid\Columns;

abstract class Column
{
    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $sortable;

    public function __construct(array $options = [])
    {
        $this->setDefaultOptions($options);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSortable()
    {
        return $this->sortable;
    }

    /**
     * @param boolean $flag
     * @return $this
     */
    public function setSortable($flag)
    {
        $this->sortable = (bool) $flag;

        return $this;
    }

    /**
     * @param array $options
     */
    protected function setDefaultOptions(array $options)
    {
        if (empty($options['name'])) {
            throw new \InvalidArgumentException(sprintf('Column MUST have name in %s class.', get_class($this)));
        }

        if (empty($options['label'])) {
            $options['label'] = camel_case($options['name']);
        }

        foreach ($options as $key => $values) {
            $methodName = 'set' . ucfirst(camel_case($key));

            if (method_exists($this, $methodName)) {
                $this->$methodName($values);
            }
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (!isset($this->$name)) {
            throw new \InvalidArgumentException(
                sprintf("Field %s does not exist in %s class", $name, get_class($this))
            );
        }

        return $this->$name;
    }
}
