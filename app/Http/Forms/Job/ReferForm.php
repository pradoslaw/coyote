<?php

namespace Coyote\Http\Forms\Job;

use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class ReferForm extends Form implements ValidatesWhenSubmitted
{
    public function buildForm()
    {
        $this
            ->add('friend_email', 'email', [
                'rules' => 'required|string|max:200|email',
                'label' => 'E-mail',
                'help' => 'Nie wysyłamy spamu! Obiecujemy.',
                'attr' => [
                    'placeholder' => 'Np. jan@kowalski.pl'
                ]
            ])
            ->add('friend_email_confirmation', 'honeypot')
            ->add('friend_name', 'text', [
                'rules' => 'required|string|max:50',
                'label' => 'Imię i nazwisko'
            ])
            ->add('email', 'email', [
                'rules' => 'nullable|string|max:200|email',
                'label' => 'Twój e-mail (opcjonalnie)'
            ])
            ->add('name', 'text', [
                'rules' => 'nullable|string|max:50',
                'label' => 'Twoje imie i nazwisko (opcjonalnie)'
            ])
            ->add('submit', 'submit', [
                'label' => 'Poleć znajomego',
                'attr' => [
                    'data-submit-state' => 'Wysyłanie...'
                ]
            ]);
    }
}
