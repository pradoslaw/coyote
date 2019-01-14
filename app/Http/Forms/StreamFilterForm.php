<?php

namespace Coyote\Http\Forms;

use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class StreamFilterForm extends Form implements ValidatesWhenSubmitted
{
    /**
     * @var array
     */
    public $attr = [
        'method' => self::GET
    ];

    public function buildForm()
    {
        $this
            ->setAttr(['id' => 'stream-filter-form'])
            ->add('text', 'search', [
                'label' => 'Szukana fraza'
            ])
            ->add('ip', 'search', [
                'label' => 'Adres IP'
            ])
            ->add('browser', 'search', [
                'label' => 'Przeglądarka'
            ])
            ->add('actor_displayName', 'search', [
                'label' => 'Użytkownik',
                'attr' => [
                    'autocomplete' => 'off'
                ]
            ])
            ->add('fingerprint', 'search', [
                'label' => 'Fingerprint'
            ])
            ->add('submit', 'submit', [
                'label' => 'Szukaj',
                'attr' => [
                    'data-submit-state' => 'Szukanie...'
                ]
            ]);
    }
}
