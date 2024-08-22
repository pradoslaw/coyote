<?php
namespace Tests\Legacy\Services\Form;

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
