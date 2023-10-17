<?php

namespace Coyote\Services\FormBuilder\Fields;

use Coyote\Services\FormBuilder\CreateFieldTrait;

class Collection extends ParentType
{
    use CreateFieldTrait;

    /**
     * @var
     */
    protected $property;

    /**
     * @return mixed
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param mixed $property
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        parent::setValue($value);

        if (!empty($this->childAttr['type'])) {
            $this->children = [];
            $this->createChildren();
        }
    }

    /**
     * Get children values as array
     *
     * @return array
     */
    public function getChildrenValues()
    {
        $values = [];

        foreach ($this->children as $child) {
            $values[] = $child->getValue();
        }

        return $values;
    }

    /**
     * Create children elements
     */
    protected function createChildren()
    {
        $type = $this->childAttr['type'] ?? null;

        if ($type === null) {
            throw new \InvalidArgumentException(
                'Collection field [' . $this->name . '] requires child_attr [type] attribute.'
            );
        }

        if (empty($this->value)) {
            return false;
        }

        $data = $this->value;
        if ($data instanceof \Illuminate\Support\Collection) {
            $data = $data->all();
        }

        // data MUST BE an array
        $data = (array)$data;
        $count = count($data);

        // reset array element's index. element could've been deleted in form (like in PRE_RENDER form event)
        // so $data can look like [2 => 'foo', 3 => 'bar']. as you can see there is no element of index 0 and 1.
        // that can cause problems in for loop.
        $data = array_values($data);

        for ($i = 0; $i < $count; $i++) {
            $field = $this->makeField($this->name . '[' . $i . ']', $type, $this->parent, $this->childAttr);
            $value = $data[$i];

            if (!($field instanceof ChildForm)) {
                if ($this->property === null) {
                    throw new \InvalidArgumentException(
                        'Collection field [' . $this->name . '] requires [property] attribute.'
                    );
                }

                $value = $this->getDataValue($data[$i], $this->property);
            }

            $field->setValue($value);
            $this->children[] = $field;
        }
    }
}
