<?php

namespace Coyote\Http\Forms\Job;

use Carbon\Carbon;
use Coyote\Country;
use Coyote\Currency;
use Coyote\Job;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;
use Illuminate\Database\Eloquent\Model;

class JobForm extends Form implements ValidatesWhenSubmitted
{
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
    ];

    public function buildForm()
    {
        $countryList = Country::pluck('name', 'id')->toArray();

        $this
            ->add('id', 'hidden')
            ->add('slug', 'hidden')
            ->add('firm_id', 'hidden')
            ->add('title', 'text', [
                'rules' => 'min:2|max:60',
                'label' => 'Tytuł oferty',
                'required' => true,
                'help' => 'Pozostało ${ charCounter(\'title\', 60) } znaków',
                'attr' => [
                    'placeholder' => 'Np. Senior Java Developer',
                    'maxlength' => 60,
                    'v-model' => 'job.title'
                ],
                'row_attr' => [
                    'class' => 'form-group form-group-border'
                ]
            ])
            ->add('country_id', 'select', [
                'rules' => 'required|integer',
                'choices' => $countryList
            ])
            ->add('city', 'text', [
                'rules' => 'string|city',
                'attr' => [
                    'placeholder' => 'Np. Wrocław, Warszawa'
                ]
            ])
            ->add('is_remote', 'checkbox', [
                'label' => 'Możliwa praca zdalna w zakresie',
                'rules' => 'bool',
                'attr' => [
                    'id' => 'remote'
                ],
                'label_attr' => [
                    'for' => 'remote'
                ]

            ])
            ->add('remote_range', 'select', [
                'rules' => 'integer|min:10|max:100',
                'choices' => Job::getRemoteRangeList(),
                'attr' => [
                    'placeholder' => '--',
                    'class' => 'input-sm input-inline',
                    'style' => 'width: 100px'
                ]
            ])
            ->add('salary_from', 'text', [
                'rules' => 'integer',
                'help' => 'Podanie tych informacji nie jest obowiązkowe, ale dzięki temu Twoja oferta zainteresuje więcej osób. Obiecujemy!',
                'attr' => [
                    'class' => 'input-inline'
                ]
            ])
            ->add('salary_to', 'text', [
                'rules' => 'integer',
                'attr' => [
                    'class' => 'input-inline'
                ]
            ])
            ->add('currency_id', 'select', [
                'rules' => 'required|integer',
                'choices' => Currency::pluck('name', 'id')->toArray(),
                'attr' => [
                    'class' => 'input-inline'
                ]
            ])
            ->add('rate_id', 'select', [
                'rules' => 'required|integer',
                'choices' => Job::getRatesList(),
                'attr' => [
                    'class' => 'input-inline'
                ]
            ])
            ->add('employment_id', 'select', [
                'rules' => 'required|integer',
                'choices' => Job::getEmploymentList(),
                'attr' => [
                    'class' => 'input-inline'
                ]
            ])
            ->add('deadline', 'text', [
                'label' => 'Data ważnosci oferty',
                'rules' => 'integer|min:1|max:365',
                'help' => 'Oferta będzie widoczna na stronie do dnia ${ deadlineDate }',
                'attr' => [
                    'class' => 'input-inline',
                    'v-model' => 'job.deadline'
                ]
            ])
            ->add('tags', 'collection', [
                'child_attr' => [
                    'type' => 'child_form',
                    'class' => TagsForm::class
                ]
            ])
            ->add('description', 'textarea', [
                'label' => 'Opis oferty',
                'help' => 'Miejsce na szczegółowy opis oferty. Pole to jednak nie jest wymagane.',
                'style' => 'height: 140px'
            ])
            ->add('enable_apply', 'choice', [
                'multiple' => false,
                'choices' => [

                    1 => 'Zezwól na wysyłanie CV poprzez serwis 4programmers.net',
                    0 => '...lub podaj informacje w jaki sposób kandydaci mogą aplikować na to stanowisko',
                ]
            ])
            ->add('recruitment', 'textarea', [
                'rules' => 'required_if:enable_apply,0|string',
                'style' => 'height: 40px'
            ])
            ->add('email', 'email', [
                'rules' => 'sometimes|required|email',
                'help' => 'Podaj adres e-mail na jaki wyślemy Ci informacje o kandydatach. Adres e-mail nie będzie widoczny dla osób postronnych.'
            ])

            ->add('submit', 'submit', [
                'label' => 'Zapisz',
                'attr' => [
                    'data-submit-state' => 'Wysyłanie...'
                ]
            ]);

        $this->setupDefaultValues();
    }

    protected function setupDefaultValues()
    {
        if ($this->data instanceof Model && !$this->data->exists) {
            $this->get('email')->setValue($this->request->user()->email);

            // @todo Uzyc mechanizmu geolokalizacji
            $this->get('country_id')->setValue(array_search('Polska', $this->get('country_id')->getChoices()));
            $this->get('remote_range')->setValue(100);
        }
    }
}
