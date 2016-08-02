<?php

namespace Boduch\Grid\Filters;

use Boduch\Grid\Column;

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
     * @param array $options
     */
    public function __construct(array $options = [])
    {
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
     * @param $operator
     * @return $this
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * @return string
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
     * @return \Collective\Html\HtmlBuilder
     */
    protected function getHtmlBuilder()
    {
        return $this->column->getGrid()->getHtmlBuilder();
    }

    /**
     * @return \Collective\Html\FormBuilder
     */
    protected function getFormBuilder()
    {
        return $this->column->getGrid()->getFormBuilder();
    }

    /**
     * @return \Illuminate\Http\Request
     */
    protected function getRequest()
    {
        return $this->column->getGrid()->getRequest();
    }

    /**
     * @return array|string
     */
    protected function getInput()
    {
        return $this->getRequest()->input($this->column->getName());
    }
}
