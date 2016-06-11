<?php

namespace Coyote\Services\Grid\Filters;

use Coyote\Services\Grid\Column;

abstract class Filter implements FilterInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $operator;

    /**
     * @var Column
     */
    protected $column;

    /**
     * @param string $operator
     * @param array $options
     */
    public function __construct($operator, array $options = [])
    {
        $this->operator = $operator;

        $this->setDefaultOptions($options);
    }

    /**
     * @return Column
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @param Column $column
     */
    public function setColumn(Column $column)
    {
        $this->column = $column;
    }

    /**
     * @return mixed
     */
    public function getOperator()
    {
        return $this->operator;
    }

    abstract public function render();

    /**
     * @param array $options
     */
    protected function setDefaultOptions(array $options)
    {
        foreach ($options as $key => $values) {
            $methodName = 'set' . ucfirst(camel_case($key));

            if (method_exists($this, $methodName)) {
                $this->$methodName($values);
            }
        }
    }

    /**
     * @param string $tag
     * @param string $content
     * @param array $attributes
     * @return \Illuminate\Support\HtmlString
     */
    protected function tag($tag, $content, array $attributes = [])
    {
        return $this->column->getGrid()->getHtmlBuilder()->tag($tag, $content, $attributes);
    }
}
