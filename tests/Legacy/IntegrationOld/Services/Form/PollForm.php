<?php
namespace Tests\Legacy\IntegrationOld\Services\Form;

class PollForm extends \Coyote\Services\FormBuilder\Form
{
    public function buildForm()
    {
        $this
            ->add('title', 'text')
            ->add('items', 'text')
            ->add('length', 'text');
    }
}
