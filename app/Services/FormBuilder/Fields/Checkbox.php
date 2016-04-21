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
    protected $checked;

    protected $checkedValue = 1;

    protected $uncheckedValue = 0;

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
     * @return int
     */
    public function getCheckedValue()
    {
        return $this->checkedValue;
    }

    /**
     * @param int $checkedValue
     */
    public function setCheckedValue($checkedValue)
    {
        $this->checkedValue = $checkedValue;
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
     */
    public function setUncheckedValue($uncheckedValue)
    {
        $this->uncheckedValue = $uncheckedValue;
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
        parent::setValue($this->checkedValue);
        $this->setChecked($this->checkedValue === $value);
    }
}
