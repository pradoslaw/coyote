<?php

namespace Coyote\Http\Forms\Job;

use Coyote\Services\FormBuilder\Form;

class GalleryForm extends Form
{
    public function buildForm()
    {
        $this
            ->add('file', 'hidden')
            ->add('url', 'hidden');
    }
}
