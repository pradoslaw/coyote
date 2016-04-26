<?php

namespace Coyote\Services\FormBuilder\Fields;

use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class ChildForm extends Field
{
    /**
     * @var string
     */
    protected $template = 'child_form';

    /**
     * @var string
     */
    protected $class;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @var Field[]
     */
    protected $children = [];

    /**
     * ChildForm constructor.
     * @param $name
     * @param $type
     * @param Form $parent
     * @param array $options
     */
    public function __construct($name, $type, Form $parent, array $options)
    {
        parent::__construct($name, $type, $parent, $options);

        if ($this->value && empty($this->children)) {
            $this->createChildren();
        }
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return \Coyote\Services\FormBuilder\Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        parent::setValue($value);

        if (!empty($this->class)) {
            $this->children = [];
            $this->createChildren();
        }
    }

    /**
     * Get a child
     *
     * @return mixed
     */
    public function getChild($key)
    {
        return array_get($this->children, $key);
    }

    /**
     * @todo Jezeli formualarz jest wysylany metoda POST to metoda createChildren() jest wywolywana 2x.
     * Raz w linii 35, w pliku ChildForm.php, a drugi raz w pliku Collection.php z linii 144 (wywolanie metody
     * setValue()).
     */
    protected function createChildren()
    {
        if (!is_string($this->class)) {
            throw new \InvalidArgumentException('Child form field [' . $this->name . '] requires [class] attribute.');
        }

        $this->form = $this->parent->getContainer()->make('form.builder')->createForm(
            $this->class,
            $this->value
        );

        if ($this->form instanceof ValidatesWhenSubmitted) {
            throw new \InvalidArgumentException(
                'Child form [' . $this->name . '] can\'t implement ValidatesWhenSubmitted interface.'
            );
        }

        $this->form->setEnableValidation(false);
        $this->children = $this->form->getFields();

        foreach ($this->children as $child) {
            $child->setName($this->name . '[' . $child->getName() . ']');
        }
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (!method_exists($this->form, $name) && is_null($this->form->get($name))) {
            throw new \BadMethodCallException(
                'Method [' . $name . '] does not exist on form [' . get_class($this->form) . ']'
            );
        }

        return call_user_func_array([$this->form, $name], $arguments);
    }

    /**
     * Get child dynamically
     *
     * @param $name
     * @return Field
     */
    public function __get($name)
    {
        return $this->getChild($name);
    }
}
