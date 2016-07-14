<?php

namespace Coyote\Http\Forms\Adm;

use Coyote\Repositories\Eloquent\UserRepository;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\FormEvents;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class StreamFilterForm extends Form implements ValidatesWhenSubmitted
{
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
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();

        $this->addEventListener(FormEvents::POST_SUBMIT, function (Form $form) use ($userRepository) {
            $username = $form->get('actor.id')->getValue();
            $form->add('user_id', 'hidden');

            if ($username) {
                /** @var \Coyote\User $user */
                $user = $userRepository->findByName($username);

                $form->get('user_id')->setValue($user->id);
            }
        });
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
            ->add('actor.id', 'text', [
                'label' => 'Użytkownik'
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
