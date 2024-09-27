<?php

namespace Coyote\Http\Forms\Wiki;

use Coyote\Services\FormBuilder\Form;

class CommentForm extends Form
{
    protected $theme = self::THEME_INLINE;

    public function buildForm()
    {
        $this->setAttr(['class' => 'comment-form']);

        $this
            ->add('text', 'textarea', [
                'rules' => 'required|string|spam_foreign:1',
                'attr'  => [
                    'placeholder' => 'Kliknij, aby dodać nowy komentarz',
                    'cols'        => 5,
                    'rows'        => 3,
                ],
            ])
            ->add('cancel', 'button', [
                'label' => 'Anuluj',
                'attr'  => [
                    'class' => 'btn btn-sm float-end btn-danger btn-cancel',
                ],
            ])
            ->add('submit', 'submit', [
                'label' => 'Zapisz',
                'attr'  => [
                    'data-submit-state' => 'Zapisywanie...',
                    'class'             => 'btn-sm float-end',
                ],
            ]);
    }
}
