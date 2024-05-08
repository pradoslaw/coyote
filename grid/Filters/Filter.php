<?php

namespace Boduch\Grid\Filters;

use Boduch\Grid\Column;
use Illuminate\Foundation\Application;

abstract class Filter implements Field
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
     * @var array
     */
    protected $attr = [];

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setDefaultOptions($options);
    }

    /**
     * @inheritdoc
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @inheritdoc
     */
    public function setColumn(Column $column)
    {
        $this->column = $column;
    }

    /**
     * @inheritdoc
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @inheritdoc
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name ?: $this->column->getName();
    }

    /**
     * @return array
     */
    public function getAttr(): array
    {
        return $this->attr;
    }

    /**
     * @param array $attr
     * @return $this
     */
    public function setAttr(array $attr)
    {
        $this->attr = $attr;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isEmpty()
    {
        if (is_array($this->getInput())) {
            return empty(array_filter($this->getInput()));
        } else {
            return !$this->hasInput();
        }
    }

    /**
     * @return string|string[]
     */
    public function getInput()
    {
        return $this->getRequest()->input($this->normalizeName($this->getName()));
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
        return $this->column->getGrid()->getGridHelper()->getHtmlBuilder();
    }

    /**
     * @return \Collective\Html\FormBuilder
     */
    protected function getFormBuilder()
    {
        return $this->column->getGrid()->getGridHelper()->getFormBuilder();
    }

    /**
     * @return \Illuminate\Http\Request
     */
    protected function getRequest()
    {
        return $this->column->getGrid()->getGridHelper()->getRequest();
    }

    /**
     * @return bool
     */
    protected function hasInput()
    {
        $method = version_compare(Application::VERSION, '5.5') === -1 ? 'has' : 'filled';

        return $this->getRequest()->$method($this->normalizeName($this->getName()));
    }

    /**
     * HTTP POST or HTTP GET can't have dots in name.
     *
     * @param string $name
     * @return string
     */
    protected function normalizeName($name)
    {
        return str_replace('.', '_', $name);
    }
}
