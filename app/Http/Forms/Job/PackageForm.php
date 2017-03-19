<?php

namespace Coyote\Http\Forms\Job;

use Coyote\Services\FormBuilder\Form;

class PackageForm extends Form
{
    public function buildForm()
    {
        $this
            ->add('length', 'text', [
                'rules' => 'required|int|min:3'
            ]);
    }
}
