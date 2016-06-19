<?php

namespace Coyote\Http\Forms\User;

use Coyote\Services\FormBuilder\Form;
use Coyote\Group;
use Coyote\User;

class AdminForm extends SettingsForm
{
    public function buildForm()
    {
        parent::buildForm();

        $this->add('skills', 'collection', [
            'label' => 'Umiejętności',
            'child_attr' => [
                'type' => 'child_form',
                'class' => SkillsForm::class,
                'value' => $this->data
            ]
        ]);
    }
}
