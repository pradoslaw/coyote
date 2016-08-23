<?php

namespace Coyote\Http\Forms;

use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class BlockForm extends Form implements ValidatesWhenSubmitted
{
    protected $theme = self::THEME_INLINE;

    public function buildForm()
    {
        $this
            ->add('name', 'text', [
                'rules' => 'required|string|max:40',
                'label' => 'Nazwa bloku',
            ])
            ->add('content', 'textarea', [
                'label' => 'Kod HTML',
                'rules' => 'required|string',
                'attr' => [
                    'id' => 'code'
                ]
            ])
            ->add('is_enabled', 'checkbox', [
                'label' => 'Włączony'
            ])
            ->add('enable_cache', 'checkbox', [
                'label' => 'Włączony cache'
            ])
            ->add('submit', 'submit_with_delete', [
                'label' => 'Zapisz',
                'attr' => [
                    'data-submit-state' => 'Zapisywanie...'
                ],
                'delete_url' => empty($this->data->id) ? '' : route('adm.blocks.delete', [$this->data->id]),
                'delete_visibility' => !empty($this->data->id)
            ]);
    }
}
