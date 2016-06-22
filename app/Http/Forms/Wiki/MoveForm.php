<?php

namespace Coyote\Http\Forms\Wiki;

use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class MoveForm extends CloneForm implements ValidatesWhenSubmitted
{
    public function buildForm()
    {
        parent::buildForm();
        
        $this->get('submit')->setLabel('Przenieś', ['attr' => ['data-submit-state' => 'Przenoszenie...']]);
    }
}
