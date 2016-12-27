<?php

namespace Coyote\Services\FormBuilder;

use Coyote\Services\FormBuilder\Fields\ChildForm;
use Coyote\Services\FormBuilder\Fields\Collection;
use Coyote\Services\FormBuilder\Fields\Field;
use Coyote\Services\FormBuilder\Fields\ParentType;

class Rules
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @param FormInterface $form
     */
    public function __construct(FormInterface $form)
    {
        $this->form = $form;
    }

    /**
     * @return array
     */
    public function getRules()
    {
        return $this->makeRules($this->form, $this->form->getFields());
    }

    /**
     * @param FormInterface|Field $context
     * @param Field[] $fields
     * @return array
     */
    protected function makeRules($context, array $fields)
    {
        $result = [];

        foreach ($fields as $field) {
            $rules = $field->getRules();

            if ($field->isRequired()) {
                $rules = explode('|', $rules);

                if (!in_array('required', $rules)) {
                    $rules = array_prepend($rules, 'required');
                }

                $rules = implode('|', $rules);
            }

            if ($rules) {
                $result[$this->transformNameToRule($context, $field)] = $rules;
            }

            // @todo: moze da sie to zrobic jakos lepiej. byc moze przeniesc ten kod do metody getRules()
            // klas dziedziczacych po Field?
            if ($field instanceof ParentType) {
                $result = array_merge($result, $this->makeRules($field, $field->getChildren()));
            }
        }

        return $result;
    }

    /**
     * Prepare laravel's rule name. Transforms string like tags[0] to tags.*
     *
     * @param FormInterface|Field $context
     * @param Field $field
     * @return string
     */
    protected function transformNameToRule($context, Field $field)
    {
        if ($context instanceof ChildForm) {
            return $this->transformNestedName($field->getName());
        } elseif ($context instanceof Collection) {
            return $this->transformArrayName($field->getName());
        } else {
            return $field->getName();
        }
    }

    /**
     * @param string $name
     * @return string
     */
    protected function transformArrayName($name)
    {
        return trim(str_replace(['[', ']'], '', preg_replace('/\[[0-9]+\]/', '.*.', $name)), '.');
    }

    /**
     * @param string $name
     * @return string
     */
    protected function transformNestedName($name)
    {
        return preg_replace('/\.[0-9]+\./', '.*.', str_replace(['[', ']'], ['.', ''], $name));
    }
}
