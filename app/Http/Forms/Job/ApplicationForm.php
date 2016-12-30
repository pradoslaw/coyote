<?php

namespace Coyote\Http\Forms\Job;

use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class ApplicationForm extends Form implements ValidatesWhenSubmitted
{
    /**
     * @var array
     */
    private $salaryChoices = [
        'od 1000 zl m-c',
        'od 2000 zl m-c',
        'od 3000 zl m-c',
        'od 4000 zl m-c',
        'od 5000 zl m-c',
        'od 6000 zl m-c',
        'od 7000 zl m-c',
        'od 8000 zl m-c',
        'od 9000 zl m-c',
        'od 10000 zl m-c',
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

    public function buildForm()
    {
        $this
            ->add('email', 'email', [
                'rules' => 'required|string|email',
                'label' => 'E-mail',
                'help' => 'Nie wysyłamy spamu! Obiecujemy.',
                'attr' => [
                    'placeholder' => 'Np. jan@kowalski.pl'
                ]
            ])
            ->add('email_confirmation', 'bot_hidden')
            ->add('name', 'text', [
                'rules' => 'required|string|max:50',
                'label' => 'Imię i nazwisko'
            ])
            ->add('phone', 'text', [
                'rules'  => 'string',
                'label' => 'Numer telefonu',
                'help' => 'Podanie numeru telefonu nie jest obowiązkowe, ale pozwoli na szybki kontakt.'
            ])
            ->add('cv', 'file', [
                'rules' => 'max:' . (config('filesystems.upload_max_size') * 1024) . '|mimes:pdf,doc,docx,rtf',
                'label' => 'CV/Resume',
                'help' => 'CV/résumé z rozszerzeniem *.pdf, *.doc, *.docx lub *.rtf.',
                'attr' => [
                    'placeholder' => 'Kliknij, aby dodać załącznik'
                ]
            ])
            ->add('salary', 'select', [
                'label' => 'Minimalne oczekiwania wynagrodzenie',
                'empty_value' => 'Do negocjacji',
                'choices' => array_combine($this->salaryChoices, $this->salaryChoices)
            ])
            ->add('text', 'textarea', [
                'rules' => 'string|required|max:5000',
                'label' => 'Wiadomość dla pracodawcy/zleceniodawcy',
                'help' => 'Taką wiadomość otrzyma osoba, która wystawiła ogłoszenie'
            ])
            ->add('cc', 'checkbox', [
                'label' => 'Wyślij kopię e-maila również do mnie',
                'value' => 1
            ])
            ->add('submit', 'submit', [
                'label' => 'Zapisz',
                'attr' => [
                    'data-submit-state' => 'Wysyłanie...'
                ]
            ]);
    }
}
