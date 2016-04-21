<?php

namespace Coyote\Services\FormBuilder\Fields;

class Select extends Text
{
    /**
     * @var array
     */
    protected $choices = [];

    /**
     * @var string
     */
    protected $emptyValue;

    /**
     * @return mixed
     */
    public function getEmptyValue()
    {
        return $this->emptyValue;
    }

    /**
     * @param mixed $emptyValue
     * @return $this
     */
    public function setEmptyValue($emptyValue)
    {
        $this->emptyValue = $emptyValue;
        return $this;
    }

    /**
     * @return array
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @param array $choices
     * @return $this
     */
    public function setChoices($choices)
    {
        $this->choices = $choices;
    }
}
