<?php

namespace Coyote\Http\Forms;

use Coyote\Repositories\Eloquent\UserRepository;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class StreamFilterForm extends Form implements ValidatesWhenSubmitted
{
    use EventsTrait;

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

    /**
     * @var UserRepository
     */
    protected $repository;

    /**
     * @param UserRepository $repository
     */
    public function __construct(UserRepository $repository)
    {
        parent::__construct();

        $this->repository = $repository;
        $this->transformUserNameToId('user_name', 'actor.id');
    }

    public function buildForm()
    {
        $this
            ->add('ip', 'text', [
                'label' => 'Adres IP'
            ])
            ->add('browser', 'text', [
                'label' => 'Przeglądarka',
                'help' => 'Użyj znaku * aby wyszukiwać po fragmencie tekstu.'
            ])
            ->add('user_name', 'text', [
                'label' => 'Użytkownik',
                'attr' => [
                    'autocomplete' => 'off'
                ]
            ])
            ->add('fingerprint', 'text', [
                'label' => 'Fingerprint'
            ])
            ->add('object.objectType', 'choice', [
                'label' => 'Obiekt',
                'choices' => array_map('ucfirst', trans()->get('stream.nouns'))
            ])
            ->add('object.id', 'text', [
                'label' => 'ID',
                'help' => 'ID użytkownika, wątku, postu itd.'
            ])
            ->add('submit', 'submit', [
                'label' => 'Szukaj',
                'attr' => [
                    'data-submit-state' => 'Szukanie...'
                ]
            ]);
    }
}
