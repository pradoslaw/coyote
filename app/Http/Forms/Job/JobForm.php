<?php

namespace Coyote\Http\Forms\Job;

use Coyote\Country;
use Coyote\Currency;
use Coyote\Job;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\FormEvents;
use Coyote\Services\Geocoder\GeocoderInterface;
use Coyote\Services\Parser\Helpers\City;
use Coyote\Tag;
use Illuminate\Database\Eloquent\Model;

class JobForm extends Form
{
    /**
     * @var string
     */
    protected $theme = self::THEME_INLINE;

    /**
     * @var Job
     */
    protected $data;

    /**
     * It's public so we can use use attr from twig
     *
     * @var array
     */
    public $attr = [
        'method' => self::POST,
    ];

    /**
     * @var GeocoderInterface
     */
    private $geocoder;

    /**
     * @param GeocoderInterface $geocoder
     */
    public function __construct(GeocoderInterface $geocoder)
    {
        parent::__construct();

        $this->geocoder = $geocoder;

        $this->addEventListener(FormEvents::POST_SUBMIT, function (JobForm $form) {
            $this->forget($this->data->tags);
            $this->forget($this->data->locations);

            // deadline not exists in table "jobs" nor in fillable array. set value so model can transform it
            // to Carbon object
            $this->data->deadline = $form->get('deadline')->getValue();

            foreach ($form->get('tags')->getChildrenValues() as $tag) {
                $pivot = $this->data->tags()->newPivot(['priority' => $tag['priority']]);
                $model = (new Tag($tag))->setRelation('pivot', $pivot);

                $this->data->tags->add($model);
            }

            $cities = (new City())->grab($form->get('city')->getValue());

            foreach ($cities as $city) {
                $this->data->locations->add(new Job\Location($this->geocode($city)));
            }
        });
    }

    public function buildForm()
    {
        $countryList = Country::pluck('name', 'id')->toArray();

        $this
            ->setAttr(['class' => 'submit-form', 'v-cloak' => 'v-cloak'])
            ->setUrl(route('job.submit'))
            ->add('id', 'hidden')
            ->add('slug', 'hidden')
            ->add('firm_id', 'hidden')
            ->add('title', 'text', [
                'rules' => 'min:2|max:60',
                'label' => 'Tytuł oferty',
                'required' => true,
                'help' => 'Pozostało <strong>${ charCounter(\'job.title\', 60) }</strong> znaków',
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
                'help' => 'Oferta będzie widoczna na stronie do dnia <strong>${ deadlineDate }</strong>',
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

                    true => 'Zezwól na wysyłanie CV poprzez serwis 4programmers.net',
                    false => '...lub podaj informacje w jaki sposób kandydaci mogą aplikować na to stanowisko',
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

    /**
     * @param string $city
     * @return array
     */
    private function geocode($city)
    {
        $location = [
            'city'          => $city
        ];

        try {
            $location = $this->geocoder->geocode($city);

            if (!$location->city) {
                $location->city = $city;
            }

            $location = $location->toArray();
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
        }

        return $location;
    }

    /**
     * @param Tag[] $collection
     */
    private function forget($collection)
    {
        foreach ($collection as $key => $model) {
            unset($collection[$key]);
        }
    }
}
