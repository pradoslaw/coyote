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
                'label' => 'E-mail kandydata',
                'help' => 'Nie wysyłamy spamu! Obiecujemy.',
                'attr' => [
                    'placeholder' => 'Np. jan@kowalski.pl'
                ]
            ])
            ->add('friend_email_confirmation', 'honeypot')
            ->add('friend_name', 'text', [
                'rules' => 'required|string|max:50',
                'label' => 'Imię i nazwisko kandydata'
            ])
            ->add('email', 'email', [
                'rules' => 'required|string|max:200|email',
                'label' => 'Twój e-mail'
            ])
            ->add('name', 'text', [
                'rules' => 'required|string|max:50',
                'label' => 'Twoje imie i nazwisko'
            ])
            ->add('submit', 'submit', [
                'label' => 'Poleć znajomemu',
                'attr' => [
                    'data-submit-state' => 'Wysyłanie...'
                ]
            ]);
    }
}
