<?php

namespace Coyote\Http\Forms;

use Carbon\Carbon;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class FirewallForm extends Form implements ValidatesWhenSubmitted
{
    public function buildForm()
    {
        $this
            ->add('name', 'text', [
                'rules' => 'sometimes|string|username|user_exist',
                'label' => 'Nazwa użytkownika'
            ])
            ->add('ip', 'text', [
                'label' => 'IP',
                'rules' => 'sometimes|string|min:2|max:100',
                'help' => 'IP może zawierać znak *'
            ])
            ->add('reason', 'textarea', [
                'label' => 'Powód',
                'rules' => 'max:1000'
            ])
            ->add('expire_at', 'text', [
                'label' => 'Data wygaśnięcia',
                'rules' => 'sometimes|date_format:Y-m-d',
                'attr' => [
                    'id' => 'expire-at'
                ]
            ])
            ->add('lifetime', 'checkbox', [
                'label' => 'Bezterminowo',
                'checked' => empty($this->data->expire_at)
            ])
            ->add('submit', 'submit', [
                'label' => 'Zapisz',
                'attr' => [
                    'data-submit-state' => 'Zapisywanie...'
                ]
            ]);

        if (empty($this->data->id)) {
            $this->get('expire_at')->setValue(Carbon::now()->addDay(1));
        }
    }
}
