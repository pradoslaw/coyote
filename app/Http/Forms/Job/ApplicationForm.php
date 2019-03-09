<?php

namespace Coyote\Http\Forms\Job;

use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\FormEvents;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class ApplicationForm extends Form implements ValidatesWhenSubmitted
{
    /**
     * @var array
     */
    private $salaryChoices = [
        'od 1000 zł m-c',
        'od 2000 zł m-c',
        'od 3000 zł m-c',
        'od 4000 zł m-c',
        'od 5000 zł m-c',
        'od 6000 zł m-c',
        'od 7000 zł m-c',
        'od 8000 zł m-c',
        'od 9000 zł m-c',
        'od 10000 zł m-c',
    ];

    /**
     * @var array
     */
    private $dismissalPeriodChoices = [
        'Brak',
        '3 dni robocze',
        '1 tydzień',
        '2 tygodnie',
        '1 miesiąc',
        '3 miesiące'
    ];

    /**
     * @var string
     */
    protected $theme = self::THEME_INLINE;

    /**
     * It's public so we can use use attr from twig
     *
     * @var array
     */
    public $attr = [
        'method' => self::POST,
        'id' => 'job-application',
        'enctype' => 'multipart/form-data'
    ];

    public function __construct()
    {
        parent::__construct();

        $this->addEventListener(FormEvents::PRE_RENDER, function (Form $form) {
            if ($form->request->session()->getOldInput('cv')) {
                $name = explode('_', $form->get('cv')->getValue(), 2)[1];

                $attr = $form->get('cv')->getAttr();
                $form->get('cv')->setAttr(array_merge($attr, ['placeholder' => $name]));
            }
        });

        $this->addEventListener(FormEvents::POST_SUBMIT, function (Form $form) {
            $github = $form->get('github')->getValue();

            if ($github) {
                if (filter_var($github, FILTER_VALIDATE_URL) === false) {
                    $form->get('github')->setValue('https://github.com/' . $github);
                }
            }
        });
    }

    public function buildForm()
    {
        $this
            ->add('email', 'email', [
                'rules' => 'required|string|max:200|email',
                'label' => 'E-mail',
                'help' => 'Nie wysyłamy spamu! Obiecujemy.',
                'attr' => [
                    'placeholder' => 'Np. jan@kowalski.pl'
                ]
            ])
            ->add('email_confirmation', 'honeypot')
            ->add('name', 'text', [
                'rules' => 'required|string|max:50',
                'label' => 'Imię i nazwisko'
            ])
            ->add('phone', 'text', [
                'rules'  => 'nullable|string|max:50',
                'label' => 'Numer telefonu',
                'help' => 'Podanie numeru telefonu nie jest obowiązkowe, ale pozwoli na szybki kontakt.'
            ])
            ->add('cv', 'hidden', [
                'label' => 'CV/Resume',
                'help' => 'CV/résumé z rozszerzeniem *.pdf, *.doc, *.docx lub *.rtf. Maksymalnie 5 MB.',
                'attr' => [
                    'placeholder' => 'Kliknij, aby dodać załącznik',
                    'id' => 'uploader',
                    'class' => 'form-control',
                    'data-upload-url' => route('job.application.upload')
                ],
                'template' => 'uploader'
            ])
            ->add('github', 'text', [
                'rules' => 'nullable|string|max:200',
                'label' => 'Konto Github',
                'help' => 'Nazwa użytkownika lub link do konta Github.',
                'row_attr' => [
                    'class' => 'github'
                ]
            ])
            ->add('salary', 'select', [
                'label' => 'Minimalne oczekiwania wynagrodzenie',
                'empty_value' => 'Do negocjacji',
                'choices' => array_combine($this->salaryChoices, $this->salaryChoices)
            ])
            ->add('dismissal_period', 'select', [
                'label' => 'Obecny okres wypowiedzenia',
                'empty_value' => 'Nie określono',
                'choices' => array_combine($this->dismissalPeriodChoices, $this->dismissalPeriodChoices)
            ])
            ->add('text', 'textarea', [
                'rules' => 'string|required|max:5000',
                'label' => 'Wiadomość dla pracodawcy/zleceniodawcy',
                'help' => 'Taką wiadomość otrzyma osoba, która wystawiła ogłoszenie'
            ])
            ->add('remember', 'checkbox', [
                'label' => 'Zapamiętaj dane podane w formularzu'
            ])
            ->add('submit', 'submit', [
                'label' => 'Zapisz',
                'attr' => [
                    'data-submit-state' => 'Wysyłanie...'
                ]
            ]);
    }
}
