<?php

namespace Coyote\Services\FormBuilder\Fields;

class Checkbox extends Field
{
    /**
     * @var string
     */
    protected $template = 'checkbox';

    /**
     * @var bool
     */
    protected $checked = false;

    /**
     * @var int
     */
    protected $checkedValue = 1;

    /**
     * @var int
     */
    protected $uncheckedValue = 0;

    /**
     * @var int
     */
//    protected $value = 1;

    /**
     * @param $flag
     * @return $this
     */
    public function setChecked($flag)
    {
        $this->setValue($flag ? $this->checkedValue : $this->uncheckedValue);

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
     * @return int
     */
    public function getUncheckedValue()
    {
        return $this->uncheckedValue;
    }

    /**
     * @param int $uncheckedValue
     * @return $this
     */
    public function setUncheckedValue($uncheckedValue)
    {
        $this->uncheckedValue = $uncheckedValue;

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        parent::setName($name);

        if (empty($this->attr['id'])) {
            $this->attr['id'] = $name;
        }

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
