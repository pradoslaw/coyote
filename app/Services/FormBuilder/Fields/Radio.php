<?php

namespace Coyote\Services\FormBuilder\Fields;

class Radio extends Field
{
    /**
     * @var string
     */
    protected $template = 'radio';

    /**
     * @var bool
     */
    protected $checked = false;

    /**
     * @var int
     */
    protected $checkedValue;

    /**
     * @param $flag
     * @return $this
     */
    public function setChecked($flag)
    {
        $this->checked = (bool) $flag;

        return $this;
    }

    /**
     * @return bool
     */
    public function getChecked()
    {
        return $this->checked;
    }

    /**
     * alias
     * @return bool
     */
    public function isChecked()
    {
        return $this->checked;
    }

    /**
     * @return int
     */
    public function getCheckedValue()
    {
        return $this->checkedValue;
    }

    /**
     * @param int $checkedValue
     * @return $this
     */
    public function setCheckedValue($checkedValue)
    {
        $this->checkedValue = $checkedValue;

        return $this;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        parent::setValue($value);
        $this->checked = $this->checkedValue == $value;
    }
}
