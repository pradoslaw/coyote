<?php

namespace Coyote\Services\FormBuilder\Fields;

class Choice extends Collection
{
    /**
     * @var array
     */
    protected $choices = [];

    /**
     * @var bool
     */
    protected $multiple = true;

    /**
     * @var bool
     */
    protected $expanded = true;

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
    public function setChoices(array $choices)
    {
        $this->choices = $choices;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setMultiple(bool $flag)
    {
        $this->multiple = $flag;

        return $this;
    }

    /**
     * @return bool
     */
    public function isExpanded(): bool
    {
        return $this->expanded;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setExpanded(bool $flag)
    {
        $this->expanded = $flag;

        return $this;
    }

    /**
     * Get children values as array
     *
     * @return array
     */
    public function getChildrenValues()
    {
        $values = [];

        foreach ($this->children as $checkbox) {
            /** @var \Coyote\Services\FormBuilder\Fields\Checkbox $checkbox */
            if (method_exists($checkbox, 'isChecked') && $checkbox->isChecked()) {
                $values[] = $checkbox->getValue();
            }
        }

        return $values;
    }

    public function createChildren()
    {
        if ($this->expanded && $this->multiple) {
            $this->buildCheckableChildren('checkbox');
        }

        if ($this->expanded && !$this->multiple) {
            $this->buildCheckableChildren('radio');
        }

        if (!$this->expanded && $this->multiple) {
            $this->buildSelect();
        }
    }

    /**
     * @param string $type
     */
    private function buildCheckableChildren($type)
    {
        $name = $this->name . ($this->multiple ? '[]' : '');

        $checkedValues = $this->value;
        if ($checkedValues instanceof \Illuminate\Support\Collection) {
            $checkedValues = $checkedValues->all();
        }

        // we must cast to array datatype because $checkedValues can be null.
        $checkedValues = (array) $checkedValues;

        if ($this->property) {
            $checkedValues = array_pluck($checkedValues, $this->property);
        }

        foreach ($this->choices as $key => $label) {
            $id = str_replace('.', '_', $this->name) . '_' . $key;

            $this->children[] = $this
                ->makeField($name, $type, $this->parent, [
                    'is_child' => true,
                    'label' => $label,
                    'checked_value' => $key,
                    'checked' => in_array($key, $checkedValues),
                    'attr' => [
                        'id' => $id
                    ],
                    'label_attr' => [
                        'for' => $id
                    ]
                ])
                ->mergeOptions($this->childAttr);
        }
    }

    private function buildSelect()
    {
        $this->children[] = $this
            ->makeField($this->name . '[]', 'select', $this->parent, [
                'choices' => $this->choices,
                'attr' => [
                    'multiple' => true
                ]
            ])
            ->mergeOptions($this->childAttr);
    }
}
