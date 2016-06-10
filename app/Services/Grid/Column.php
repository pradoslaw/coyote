<?php

namespace Coyote\Services\Grid;

use Coyote\Services\Grid\Decorators\DecoratorInterface;
use Coyote\Services\Grid\Decorators\Link;

class Column
{
    /**
     * @var Grid
     */
    protected $grid;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $sortable = false;

    /**
     * @var DecoratorInterface[]
     */
    protected $decorators = [];

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setDefaultOptions($options);
    }

    /**
     * @param Grid $grid
     * @return $this
     */
    public function setGrid($grid)
    {
        $this->grid = $grid;

        return $this;
    }

    /**
     * @return Grid
     */
    public function getGrid()
    {
        return $this->grid;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

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
     * @param \Closure $closure
     */
    public function setClickable(\Closure $closure)
    {
        $this->addDecorator((new Link())->render($closure));
    }

    /**
     * @param DecoratorInterface $decorator
     * @return $this
     */
    public function addDecorator(DecoratorInterface $decorator)
    {
        $this->decorators[] = $decorator;

        return $this;
    }

    /**
     * @param DecoratorInterface[] $decorators
     * @return $this
     */
    public function setDecorators(array $decorators)
    {
        $this->decorators = $decorators;

        return $this;
    }

    /**
     * @return \Coyote\Services\Grid\Decorators\DecoratorInterface[]
     */
    public function getDecorators()
    {
        return $this->decorators;
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
        $name = camel_case($name);

        if (!isset($this->$name)) {
            throw new \InvalidArgumentException(
                sprintf("Field %s does not exist in %s class", $name, get_class($this))
            );
        }

        return $this->$name;
    }
}
