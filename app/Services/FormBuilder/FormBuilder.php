<?php

namespace Coyote\Services\FormBuilder;

class FormBuilder
{
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

        $form = app()->make($formClass)->setData($data)->setOptions($options);
        $form->buildForm();

        return $form;
    }
}
