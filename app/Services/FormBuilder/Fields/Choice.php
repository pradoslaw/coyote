<?php

namespace Coyote\Services\FormBuilder\Fields;

class Choice extends Collection
{
    /**
     * @var array
     */
    protected $choices = [];

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
     * Get children values as array
     *
     * @return array
     */
    public function getChildrenValues()
    {
        $values = [];

        foreach ($this->children as $checkbox) {
            /** @var \Coyote\Services\FormBuilder\Fields\Checkbox $checkbox */
            if ($checkbox->isChecked()) {
                $values[] = $checkbox->getValue();
            }
        }

        return $values;
    }

    /** @todo ability of creating list of radio buttons */
    public function createChildren()
    {
        $this->buildCheckboxes();
    }

    /**
     * Create set of checkboxes
     */
    private function buildCheckboxes()
    {
        $name = $this->name . '[]';

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

            $this->children[] = $this->makeField($name, 'checkbox', $this->parent, $this->childAttr + [
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
            ]);
        }
    }
}
