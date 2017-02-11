<?php

namespace Coyote\Http\Forms\Job;

use Coyote\Services\FormBuilder\Form;

class FeaturesForm extends Form
{
    public function buildForm()
    {
        $this
            ->add('id', 'hidden', [
                'rules' => 'required|int'
            ])
            ->add('name', 'text', [
                'rules' => 'string|max:100'
            ])
            ->add('name', 'hidden', [
                'rules' => 'string|max:100'
            ])
            ->add('is_checked', 'checkbox', [
                'rules' => 'bool'
            ]);
    }
}
