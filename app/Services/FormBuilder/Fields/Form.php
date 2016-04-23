<?php

namespace Coyote\Services\FormBuilder\Fields;

use Coyote\Services\FormBuilder\Form as ParentForm;

class Form extends Field
{
    protected $class;
    protected $form;
    protected $data;

    public function __construct($name, $type, ParentForm $parent, array $options)
    {
        parent::__construct($name, $type, $parent, $options);
        $this->createChildren();
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param mixed $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return mixed
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    protected function createChildren()
    {
        if (!is_string($this->class)) {
            throw new \InvalidArgumentException('Please provide full name of Form class.');
        }

        $this->form = $this->parent->getContainer()->make('form.builder')->createForm($this->class, $this->data);
    }
    
    public function __call($name, $arguments)
    {
        if (!method_exists($this->form, $name)) {
            throw new \BadMethodCallException(
                'Method [' . $name . '] does not exist on form [' . get_class($this->form) . ']'
            );
        }
        
        return call_user_func_array([$this->form, $name], $arguments);
    }
}
