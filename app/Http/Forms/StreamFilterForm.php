<?php

namespace Coyote\Http\Forms;

use Coyote\Repositories\Eloquent\UserRepository;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class StreamFilterForm extends Form implements ValidatesWhenSubmitted
{
    use EventsTrait;

    const HOUR          = 60 * 60;
    const DAY           = self::HOUR * 24;
    const WEEK          = self::DAY * 7;
    const TWO_WEEKS     = self::WEEK * 2;
    const MONTH         = self::WEEK * 4;
    const HALF_YEAR     = self::MONTH * 6;
    const YEAR          = self::MONTH * 12;

    /**
     * @var array
     */
    public $attr = [
        'method' => self::GET
    ];

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
            ->add('created_at', 'select', [
                'label' => 'Data i czas',
                'empty_value' => 'Wszystkie wpisy',
                'choices' => [
                    self::HOUR => 'Ostatnia godzina',
                    self::DAY => 'Ostatnie 24 godz.',
                    self::WEEK => 'Ostatni tydzień',
                    self::TWO_WEEKS => 'Ostatnie 2 tyg.',
                    self::MONTH => 'Ostatnie 4 tyg.',
                    self::HALF_YEAR => 'Ostatnie 6 miesięcy',
                    self::YEAR => 'Ostatni rok'
                ]
            ])
            ->add('submit', 'submit', [
                'label' => 'Szukaj',
                'attr' => [
                    'data-submit-state' => 'Szukanie...'
                ]
            ]);
    }
}
