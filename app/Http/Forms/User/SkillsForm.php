<?php

namespace Coyote\Http\Forms\User;

use Coyote\Services\FormBuilder\Form;
use Coyote\Group;
use Coyote\User;

class SkillsForm extends Form
{
    public function buildForm()
    {
        $this
            ->add('name', 'text', [
                'label' => 'Nazwa',
                'rules' => 'required|string|max:100|unique:user_skills,name,NULL,id,user_id,' . $this->data->id
            ])
            ->add('rate', 'text', [
                'label' => 'Ocena',
                'rules' => 'required|integer|min:1|max:6'
            ])
            ->add('order', 'hidden');
    }
}
