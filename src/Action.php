<?php

namespace Boduch\Grid;

class Action extends Cell
{
    /**
     * @var Components\RowAction[]
     */
    protected $rowActions;

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        throw new \InvalidArgumentException('Can\'t set action value in action column.');
    }

    /**
     * @return Components\RowAction[]
     */
    public function getRowActions(): array
    {
        return $this->rowActions;
    }

    /**
     * @param Components\RowAction[] $rowActions
     * @return $this
     */
    public function setRowActions(array $rowActions)
    {
        $this->rowActions = $rowActions;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        $html = '';

        foreach ($this->rowActions as $rowAction) {
            $html .= $rowAction->setData($this->data)->render();
        }

        return $html;
    }

    protected function decorate()
    {
        return null;
    }

    protected function setupValue()
    {
        //
    }
}
