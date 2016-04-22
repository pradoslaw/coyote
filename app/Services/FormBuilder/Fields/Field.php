<?php

namespace Coyote\Services\FormBuilder\Fields;

use Coyote\Services\FormBuilder\Form;
use Illuminate\View\View;

abstract class Field
{
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

        $this->setDefaultOptions($options);
        $this->setupValue();
    }

    protected function setupValue()
    {
        if ($this->value === null) {
            $this->setValue($this->getDataValue($this->name));
        }
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
     * @return array|null
     */
    public function getErrors()
    {
        return $this->parent->errors() ? $this->parent->errors()->get($this->name) : null;
    }

    /**
     * @return string|null
     */
    public function getError()
    {
        return $this->parent->errors() ? $this->parent->errors()->first($this->name) : null;
    }

    /**
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param string $theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    /**
     * @param $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param $view
     * @param array $data
     * @return View
     */
    public function view($view, $data = [])
    {
        return view($this->getTheme() . '.' . $view, $data);
    }

    /**
     * @return string
     * @throws \Exception
     * @throws \Throwable
     */
    public function renderLabel()
    {
        return $this->view('label', $this->fieldData())->render();
    }

    /**
     * @return string
     * @throws \Exception
     * @throws \Throwable
     */
    public function renderWidget()
    {
        return $this->view($this->getType() . '_widget', $this->fieldData())->render();
    }

    /**
     * @return string
     * @throws \Exception
     * @throws \Throwable
     */
    public function renderError()
    {
        return $this->view('error', $this->fieldData())->render();
    }

    /**
     * Render entire element
     *
     * @return mixed
     */
    public function render()
    {
        return $this->view($this->getTemplate(), $this->fieldData())->render();
    }

    /**
     * @param $name
     * @return mixed|null
     */
    protected function getDataValue($name)
    {
        if ($this->parent->isSubmitted()) {
            return $this->parent->getRequest()->get($name);
        } else {
            $data = $this->parent->getData();

            if (is_object($data)) {
                return $data->$name ?? null;
            } elseif (is_array($data)) {
                return $data[$name] ?? null;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    protected function fieldData()
    {
        $result = [];

        $reflection = new \ReflectionClass($this);

        foreach ($reflection->getMethods() as $method) {
            $name = $method->getName();

            if (substr($name, 0, 3) === 'get' && $method->getNumberOfParameters() === 0) {
                $withoutPrefix = substr($name, 3);
                $result[snake_case($withoutPrefix)] = $this->$name();
            }
        }

        return $result;
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
     * @return mixed
     */
    public function __toString()
    {
        return $this->getValue();
    }
}
