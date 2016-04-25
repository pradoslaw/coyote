<?php

namespace Coyote\Services\FormBuilder\Fields;

use Coyote\Services\FormBuilder\CreateFieldTrait;
use Coyote\Services\FormBuilder\Form as ParentForm;

class Collection extends Field
{
    use CreateFieldTrait;

    /**
     * @var
     */
    protected $property;

    /**
     * @var Field[]
     */
    protected $childAttr = [];

    /**
     * @var array
     */
    protected $children = [];

    /**
     * @param $name
     * @param $type
     * @param ParentForm $parent
     * @param array $options
     */
    public function __construct($name, $type, ParentForm $parent, array $options)
    {
        parent::__construct($name, $type, $parent, $options);

        if ($this->value && !$this->children) {
            $this->createChildren();
        }
    }

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
     * @return array
     */
    public function getChildAttr()
    {
        return $this->childAttr;
    }

    /**
     * @param array $childAttr
     */
    public function setChildAttr($childAttr)
    {
        $this->childAttr = $childAttr;
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
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
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

        if (is_null($type)) {
            throw new \InvalidArgumentException(
                'Collection field [' . $this->name . '] requires child_attr [type] attribute.'
            );
        }

        $data = $this->value;
        if ($data instanceof \Illuminate\Support\Collection) {
            $data = $data->all();
        }

        $count = count($data);
        for ($i = 0; $i < $count; $i++) {
            $field = $this->makeField($this->name . '[' . $i . ']', $type, $this->parent, $this->childAttr);
            $value = $data[$i];

            if (!($field instanceof ChildForm)) {
                if (is_null($this->property)) {
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
