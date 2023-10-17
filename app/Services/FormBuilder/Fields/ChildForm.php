<?php

namespace Coyote\Services\FormBuilder\Fields;

use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class ChildForm extends ParentType
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
     * @todo Jezeli formualarz jest wysylany metoda POST to metoda createChildren() jest wywolywana 2x.
     * Raz w linii 35, w pliku ChildForm.php, a drugi raz w pliku Collection.php z linii 144 (wywolanie metody
     * setValue()).
     */
    protected function createChildren()
    {
        $this->form = $this->createForm();

        if ($this->form instanceof ValidatesWhenSubmitted) {
            throw new \InvalidArgumentException(
                'Child form [' . $this->name . '] can\'t implement ValidatesWhenSubmitted interface.'
            );
        }

        $this->form->setEnableValidation(false);
        $this->children = $this->form->getFields();

        /** @var Field $child */
        foreach ($this->children as $child) {
            $child->setName($this->name . '[' . $child->getName() . ']');
        }
    }

    /**
     * @return Form
     */
    protected function createForm()
    {
        if ($this->class === null) {
            throw new \InvalidArgumentException('Child form field [' . $this->name . '] requires [class] attribute.');
        }
        if ($this->class instanceof Form) {
            $this->form = $this->class;
            $this->class = class_basename($this->form);

            if (null !== $this->value) {
                $this->form->setData($this->value);
            }
        } else if (is_string($this->class)) {
            $this->form = $this->parent->getContainer()->make('form.builder')->createForm(
                $this->class,
                $this->value
            );
        } else {
            throw new \InvalidArgumentException('Child form [class] attribute passed in wrong format.');
        }

        return $this->form;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (!method_exists($this->form, $name) && $this->form->get($name) === null) {
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
