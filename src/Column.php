<?php

namespace Boduch\Grid;

use Boduch\Grid\Decorators\DecoratorInterface;
use Boduch\Grid\Decorators\Link;
use Boduch\Grid\Decorators\Html;
use Boduch\Grid\Decorators\Placeholder;
use Boduch\Grid\Filters\FilterInterface;

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
     * @var FilterInterface
     */
    protected $filter;

    /**
     * @var string
     */
    protected $placeholder;

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
     * @param \Closure $closure
     */
    public function setRender(\Closure $closure)
    {
        $this->addDecorator((new Html())->render($closure));
    }

    /**
     * @param string $placeholder
     * @return $this
     */
    public function setPlaceholder($placeholder)
    {
        $this->addDecorator(new Placeholder($placeholder));
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
     * @return DecoratorInterface[]
     */
    public function getDecorators()
    {
        return $this->decorators;
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function setFilter(FilterInterface $filter)
    {
        $filter->setColumn($this);
        $this->filter = $filter;

        return $this;
    }

    /**
     * @return FilterInterface
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @return bool
     */
    public function isFilterable()
    {
        return $this->filter !== null;
    }

    /**
     * @param array $options
     *
     * @todo Jezeli podczas budowania tabeli, uzyjemy metody setClickable(), a nastepnie setDecorators() to
     * ten pierwszy deokrator zostnie usuniety
     */
    protected function setDefaultOptions(array $options)
    {
        if (empty($options['name'])) {
            throw new \InvalidArgumentException(sprintf('Column MUST have name in %s class.', get_class($this)));
        }

        if (empty($options['label'])) {
            $options['label'] = camel_case($options['name']);
        }

        // placeholder MUST be the first element in options array. that's because "placeholder" decorator
        // can break further decorators.
        $placeholder = array_pull($options, 'placeholder');
        if (!empty($placeholder)) {
            $options = array_merge(['placeholder' => $placeholder], $options);
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
