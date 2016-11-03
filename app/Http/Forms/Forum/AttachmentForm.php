<?php

namespace Coyote\Http\Forms\Forum;

use Coyote\Services\FormBuilder\Form;

class AttachmentForm extends Form
{
    /**
     * @var string
     */
    protected $theme = 'forum.forms';

    /**
     * @var string
     */
    protected $template = 'attachment';

    /**
     * @var bool
     */
    protected $enableValidation = false;

    /**
     * @var array
     *
     * @todo ustawiamy formularz jako HTTP PUT przez co nie bedzie walidowany podczas dodawania zalacznikow.
     * to jest bug i nie powinno byc to konieczne. do poprawy!
     */
    public $attr = ['method' => 'PUT'];

    public function buildForm()
    {
        $this
            ->add('id', 'hidden')
            ->add('file', 'hidden')
            ->add('name', 'control')
            ->add('mime', 'control')
            ->add('created_at', 'control')
            ->add('size', 'control');
    }
}
