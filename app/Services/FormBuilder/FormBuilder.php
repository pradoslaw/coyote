<?php

namespace Coyote\Services\FormBuilder;

use Illuminate\Contracts\Container\Container;

class FormBuilder
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container  $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $formClass
     * @param mixed $data
     * @param array $options
     * @return \Coyote\Services\FormBuilder\Form
     */
    public function createForm($formClass, $data = null, array $options = [])
    {
        if (!class_exists($formClass)) {
            throw new \InvalidArgumentException(
                'Form class with name ' . $formClass . ' does not exist.'
            );
        }

        /** @var FormInterface $form */
        $form = $this->container->make($formClass)->setData($data)->setOptions($options);
        $form->buildForm();

        return $form;
    }
}
