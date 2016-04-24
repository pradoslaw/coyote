<?php

namespace Coyote\Services\FormBuilder\Fields;

use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\RenderTrait;
use Illuminate\Database\Eloquent\Model;

abstract class Field
{
    use RenderTrait;
    
    const DEFAULT_TEMPLATE = 'row';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var Form
     */
    protected $parent;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $help;

    /**
     * @var string
     */
    protected $theme;

    /**
     * @var string
     */
    protected $template = self::DEFAULT_TEMPLATE;

    /**
     * @var string
     */
    protected $rules;

    /**
     * @var array
     */
    protected $attr = [];

    /**
     * @var array
     */
    protected $labelAttr = [];

    /**
     * @var array
     */
    protected $rowAttr = [];

    /**
     * Name of the property for value setting
     *
     * @var string
     */
    protected $valueProperty = 'value';

    /**
     * @var bool
     */
    protected $required = false;

    /**
     * Field constructor.
     * @param $name
     * @param $type
     * @param Form $parent
     * @param array $options
     */
    public function __construct($name, $type, Form $parent, array $options = [])
    {
        $this->setName($name);
        $this->setType($type);
        $this->setParent($parent);

        // 1) Set up options (attributes, field value)
        $this->setDefaultOptions($options);
        // 2) Set up the value (from model, request, session etc) if it wasn't set before
        $this->setupValue();
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @param string $type
     * @return $this
     */
    protected function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param Form $parent
     * @return $this
     */
    protected function setParent(Form $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return Form
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @param $rules
     * @return $this
     */
    public function setRules($rules)
    {
        $this->rules = $rules;

        if ($rules) {
            $rules = explode('|', $rules);

            if (in_array('required', $rules)) {
                $this->setRequired(true);
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @return mixed
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * @param mixed $help
     */
    public function setHelp($help)
    {
        $this->help = $help;
    }

    /**
     * @return array
     */
    public function getAttr()
    {
        return $this->attr;
    }

    /**
     * @param array $attr
     */
    public function setAttr($attr)
    {
        $this->attr = $attr;
    }

    /**
     * @return array
     */
    public function getLabelAttr()
    {
        return $this->labelAttr;
    }

    /**
     * @param array $labelAttr
     */
    public function setLabelAttr($labelAttr)
    {
        $this->labelAttr = $labelAttr;
    }

    /**
     * @return array
     */
    public function getRowAttr()
    {
        return $this->rowAttr;
    }

    /**
     * @param array $rowAttr
     */
    public function setRowAttr($rowAttr)
    {
        $this->rowAttr = $rowAttr;
    }

    /**
     * @return boolean
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Method alias
     *
     * @return bool
     */
    public function getRequired()
    {
        return $this->isRequired();
    }

    /**
     * @param boolean $required
     * @return $this
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function mergeOptions(array $options)
    {
        $reflection = new \ReflectionClass($this);

        foreach ($options as $key => $values) {
            $baseName = ucfirst(camel_case($key));
            $setter = 'set' . $baseName;

            if (method_exists($this, $setter)) {
                $getter = 'get' . $baseName;

                if ($reflection->hasMethod($getter)
                    && $reflection->getMethod($getter)->getNumberOfParameters() === 0) {
                    $currentValue = $this->$getter();

                    if (is_array($currentValue)) {
                        $values = array_merge_recursive($currentValue, $values);
                    }
                }
                $this->$setter($values);
            }
        }

        return $this;
    }

    /**
     * @return array|null
     */
    public function getErrors()
    {
        return $this->parent->errors() ? $this->parent->errors()->get($this->transformToDotSyntax($this->name)) : null;
    }

    /**
     * @return string|null
     */
    public function getError()
    {
        return $this->parent->errors() ? $this->parent->errors()->first($this->transformToDotSyntax($this->name)) : null;
    }

    /**
     * @return string
     * @throws \Exception
     * @throws \Throwable
     */
    public function renderLabel()
    {
        return $this->view($this->getViewPath('label'), $this->viewData())->render();
    }

    /**
     * @return string
     * @throws \Exception
     * @throws \Throwable
     */
    public function renderWidget()
    {
        return $this->view($this->getWidgetPath($this->getWidgetName()), $this->viewData())->render();
    }

    /**
     * @return string
     * @throws \Exception
     * @throws \Throwable
     */
    public function renderError()
    {
        return $this->view($this->getViewPath('error'), $this->viewData())->render();
    }

    /**
     * Render entire element
     *
     * @return mixed
     */
    public function render()
    {
        return $this->view($this->getViewPath($this->getTemplate()), $this->viewData())->render();
    }

    /**
     * Setup field value when initialized
     */
    protected function setupValue()
    {
        if ($this->parent->isSubmitted()) {
            $this->setValue($this->parent->getRequest()->get($this->name));
        } elseif ($this->hasOldInput($this->name)) {
            $this->setValue($this->getOldInput($this->name));
        } elseif ($this->value === null) {
            $this->setValue($this->getDataValue($this->parent->getData(), $this->name));
        }
    }

    /**
     * @param mixed $data
     * @param string $name
     * @return mixed|null
     *
     * @todo ta nazwa jest mylaca bo sugeruje, ze pobieramy dane tylko z danych przekazanych
     * do formularza, ale pobieramy rowniez z sesji oraz z POST
     */
    protected function getDataValue($data, $name)
    {
        $name = $this->transformToDotSyntax($name);

        if (is_string($data)) {
            return $data;
        } elseif (is_object($data)) {
            return object_get($this->loadModelRelation($data, $name), $name);
        } elseif (is_array($data)) {
            return array_get($data, $name);
        }

        return $this->getValue();
    }

    /**
     * @param $key
     * @return mixed
     */
    protected function getOldInput($key)
    {
        return $this->parent->getRequest()->session()->getOldInput($key);
    }

    /**
     * @param $key
     * @return bool
     */
    protected function hasOldInput($key)
    {
        return $this->parent->getRequest()->session()->hasOldInput($key);
    }

    /**
     * If object is a instance of Eloquent model, we have to make sure that relations were loaded
     *
     * @param $model
     * @param $key
     * @return mixed
     */
    protected function loadModelRelation($model, $key)
    {
        if (!($model instanceof Model)) {
            return $model;
        }

        if (!isset($model->$key) && method_exists($model, $key)) {
            $model->getRelationValue($key);
        }

        return $model;
    }

    /**
     * @return array
     */
    protected function viewData()
    {
        $result = [];

        $reflection = new \ReflectionClass($this);

        foreach ($reflection->getMethods() as $method) {
            $name = $method->getName();

            if (substr($name, 0, 3) === 'get' && $method->getNumberOfParameters() === 0 && !$method->isPrivate()) {
                $withoutPrefix = substr($name, 3);
                $result[snake_case($withoutPrefix)] = $this->$name();
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function getWidgetName()
    {
        return $this->getType() . '_widget';
    }

    /**
     * @param array $options
     */
    protected function setDefaultOptions(array $options)
    {
        foreach ($options as $key => $values) {
            $methodName = 'set' . ucfirst(camel_case($key));

            if (method_exists($this, $methodName)) {
                $this->$methodName($values);
            }
        }
    }

    /**
     * @param string $string
     * @return string
     */
    public function transformToDotSyntax($string)
    {
        return str_replace(['.', '[]', '[', ']'], ['_', '', '.', ''], $string);
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->getValue();
    }
}
