<?php

namespace Coyote\Services\FormBuilder\Fields;

use Coyote\Services\FormBuilder\Form;

class Honeypot extends Field
{
    /**
     * Field constructor.
     * @param $name
     * @param $type
     * @param Form $parent
     * @param array $options
     */
    public function __construct($name, $type, Form $parent, array $options = [])
    {
        parent::__construct($name, $type, $parent, $options);

        $this->setRules('max:0');
        $this->setLabel('Email (ponownie)');
        $this->setAttr(['placeholder' => 'Pozostaw to pole puste!']);
        $this->setRowAttr(['class' => 'd-none']);
    }
}
