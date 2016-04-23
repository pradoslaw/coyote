<?php

namespace TwigBridge\Extensions;

use Coyote\Services\FormBuilder\Fields\Field;
use Coyote\Services\FormBuilder\Fields\Form as ChildForm;
use Coyote\Services\FormBuilder\Form;
use Twig_Extension;
use Twig_SimpleFunction;

class FormBuilder extends Twig_Extension
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'TwigBridge_Extension_FormBuilder';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('form', [&$this, 'form'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('form_start', [&$this, 'formStart'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('form_end', [&$this, 'formEnd'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('form_row', [&$this, 'formRow'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('form_label', [&$this, 'formLabel'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('form_widget', [&$this, 'formWidget'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('form_error', [&$this, 'formError'], ['is_safe' => ['html']]),
        ];
    }

    public function form($form)
    {
        if ($form instanceof Form) {
            return $form->render();
        } elseif ($form instanceof ChildForm) {
            return $form->getForm()->render();
        }
        
        return null;
    }

    public function formStart(Form $form)
    {
        return $form->renderForm();
    }

    public function formEnd()
    {
        return app('form')->close();
    }

    /**
     * @param Field $field
     * @param array $options
     * @return mixed|null
     */
    public function formRow($field, array $options = [])
    {
        return $this->renderFormElement('render', $field, $options);
    }

    // @todo zastapic przez intefejs
    public function formLabel($field, array $options = [])
    {
        return $this->renderFormElement('renderLabel', $field, $options);
    }

    /**
     * @param Field $field
     * @param array $options
     * @return string
     */
    public function formWidget($field, array $options = [])
    {
        return $this->renderFormElement('renderWidget', $field, $options);
    }

    /**
     * @param $field
     * @param array $options
     * @return null
     */
    public function formError($field, array $options = [])
    {
        return $this->renderFormElement('renderError', $field, $options);
    }

    /**
     * @param $element
     * @param $field
     * @param array $options
     * @return null
     */
    private function renderFormElement($element, $field, $options = [])
    {
        if ($field instanceof Field) {
            if (!empty($options)) {
                $field->mergeOptions($options);
            }

            return $field->$element();
        }

        return null;
    }
}
