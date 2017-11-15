<?php

namespace Coyote\Http\Forms;

use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class MailingForm extends Form implements ValidatesWhenSubmitted
{
    public function buildForm()
    {
        $this
            ->add('subject', 'text', [
                'rules' => 'required|max:100',
                'label' => 'Temat'
            ])
            ->add('text', 'textarea', [
                'rules' => 'required',
                'label' => 'Treść'
            ])
            ->add('is_demo', 'checkbox', [
                'label' => 'Wyślij testowego e-maila'
            ])
            ->add('submit', 'submit', [
                'label' => 'Wyślij',
                'attr' => [
                    'data-submit-state' => 'Wysyłanie...'
                ]
            ]);
    }
}
