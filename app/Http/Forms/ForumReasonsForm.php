<?php

namespace Coyote\Http\Forms;

use Coyote\Services\FormBuilder\Form;

class ForumReasonsForm extends Form
{
    public function buildForm()
    {
        $this
            ->add('name', 'text', [
                'label' => 'Nazwa',
                'rules' => 'required|string|min:2|max:100'
            ])
            ->add('description', 'textarea', [
                'rules' => 'string',
                'label' => 'Opis',
                'help' => 'Zawartość tego pola może być wysyłana do użytkowników (w formie e-maila lub powiadomienia).'
            ])
            ->add('submit', 'submit_with_delete', [
                'label' => 'Zapisz',
                'attr' => [
                    'data-submit-state' => 'Zapisywanie...'
                ],
                'delete_url' => empty($this->data->id) ? '' : route('adm.forum.reasons.delete', [$this->data->id]),
                'delete_visibility' => !empty($this->data->id)
            ]);
    }
}
