<?php

namespace Boduch\Grid\Filters;

class DateRange extends Filter
{
    /**
     * @var string
     */
    protected $template = 'laravel-grid::filters.date_range';

    /**
     * @var string
     */
    protected $separator = ' - ';

    /**
     * @return string
     */
    public function render()
    {
        return $this->column->getGrid()->getGridHelper()->getView()->make($this->template, [
            'name' => $this->getName(),
            'input' => $this->getInput(),
            'separator' => $this->separator
        ]);
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @param string $template
     * @return $this
     */
    public function setTemplate(string $template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return string
     */
    public function getSeparator(): string
    {
        return $this->separator;
    }

    /**
     * @param string $separator
     * @return $this
     */
    public function setSeparator(string $separator)
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        if (!empty($this->getInput()['from']) && !empty($this->getInput()['to'])) {
            return FilterOperator::OPERATOR_BETWEEN;
        } elseif (empty($this->getInput()['to'])) {
            return FilterOperator::OPERATOR_GTE;
        } else {
            return FilterOperator::OPERATOR_LTE;
        }
    }
}
