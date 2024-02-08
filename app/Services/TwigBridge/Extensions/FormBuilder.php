<?php

namespace Coyote\Services\TwigBridge\Extensions;

use Coyote\Services\FormBuilder\Fields\ChildForm as ChildForm;
use Coyote\Services\FormBuilder\Fields\Field;
use Coyote\Services\FormBuilder\Form;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FormBuilder extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('form', [&$this, 'form'], ['is_safe' => ['html']]),
            new TwigFunction('form_start', [&$this, 'formStart'], ['is_safe' => ['html']]),
            new TwigFunction('form_end', [&$this, 'formEnd'], ['is_safe' => ['html']]),
            new TwigFunction('form_row', [&$this, 'formRow'], ['is_safe' => ['html']]),
            new TwigFunction('form_label', [&$this, 'formLabel'], ['is_safe' => ['html']]),
            new TwigFunction('form_widget', [&$this, 'formWidget'], ['is_safe' => ['html']]),
            new TwigFunction('form_error', [&$this, 'formError'], ['is_safe' => ['html']]),
        ];
    }

    public function form($form)
    {
        if ($form instanceof Form) {
            return $form->render();
        }
        if ($form instanceof ChildForm) {
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

    /**
     * @param $field
     * @param array $options
     * @return mixed|null
     */
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
