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

    /** @todo ability of creating list of radio buttons */
    public function createChildren()
    {
        $this->createCheckboxes();
    }

    /**
     * Create set of checkboxes
     */
    private function createCheckboxes()
    {
        $name = $this->name . '[]';

        $data = $this->value;
        if ($data instanceof \Illuminate\Support\Collection) {
            $data = $data->all();
        }

        if ($this->property) {
            // we must cast to array datatype because $data can be null.
            $data = array_pluck((array) $data, $this->property);
        }

        foreach ($this->choices as $key => $label) {
            $id = str_replace('.', '_', $this->name) . '_' . $key;

            $this->children[] = $this->makeField($name, 'checkbox', $this->parent, $this->childAttr + [
                'label' => $label,
                'checked_value' => $key,
                'checked' => in_array($key, $data),
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
