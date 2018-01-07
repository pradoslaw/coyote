<?php

namespace Coyote\Http\Forms\Job;

use Coyote\Services\FormBuilder\Form;

class TagsForm extends Form
{
    public function buildForm()
    {
        $this
            ->add('name', 'text', [
                'rules' => 'max:50|tag'
            ])
            ->add('priority', 'text', [
                'rules' => 'required|int|min:0|max:2'
            ]);
    }
}
