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

    /**
     * @var string
     */
    protected $theme = 'forms.themes.filter';

    public function buildForm()
    {
        $this
            ->setAttr(['id' => 'stream-filter-form'])
            ->add('ip', 'text', [
                'label' => 'Adres IP'
            ])
            ->add('browser', 'text', [
                'label' => 'Przeglądarka',
                'help' => 'Użyj znaku * aby wyszukiwać po fragmencie tekstu.'
            ])
            ->add('actor_displayName', 'text', [
                'label' => 'Użytkownik',
                'attr' => [
                    'autocomplete' => 'off'
                ]
            ])
            ->add('fingerprint', 'text', [
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
