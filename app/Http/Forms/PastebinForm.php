<?php

namespace Coyote\Http\Forms;

use Coyote\Services\FormBuilder\Form;

class PastebinForm extends Form
{
    public function buildForm()
    {
        $this
            ->add('content', 'text')
            ->add('syntax', 'select', [
                'choices' => $this->getSyntaxList(),
                'label' => 'Kolorowanie składni'
            ])
            ->add('expires', 'select', [
                'choices' => $this->getExpiresList(),
                'label' => 'Wygaśnie'
            ]);
    }

    private function getSyntaxList()
    {
        return [];
    }

    public function getExpiresList()
    {
        return [];
    }
}
