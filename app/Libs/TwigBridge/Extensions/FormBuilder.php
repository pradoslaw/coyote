<?php

namespace TwigBridge\Extensions;

use Coyote\Services\FormBuilder\Fields\Field;
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

    public function form(Form $form)
    {
        return $form->render();
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
     * @return mixed|null
     */
    public function formRow($field)
    {
        if ($field instanceof Field) {
            return $field->render();
        }

        return null;
    }

    // @todo zastapic przez intefejs
    public function formLabel(Field $field)
    {
        return $field->renderLabel();
    }

    public function formWidget(Field $field)
    {
        return $field->renderWidget();
    }

    public function formError(Field $field)
    {
        return $field->renderError();
    }
}
