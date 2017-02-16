<?php

namespace Coyote\Http\Forms\Job;

use Coyote\Country;
use Coyote\Currency;
use Coyote\Job;
use Coyote\Repositories\Contracts\FeatureRepositoryInterface as FeatureRepository;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\FormEvents;
use Coyote\Services\Geocoder\GeocoderInterface;
use Coyote\Services\Parser\Helpers\City;
use Coyote\Tag;

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
     * @var FeatureRepository
     */
    private $feature;

    /**
     * @param GeocoderInterface $geocoder
     * @param FeatureRepository $feature
     */
    public function __construct(GeocoderInterface $geocoder, FeatureRepository $feature)
    {
        parent::__construct();

        $this->geocoder = $geocoder;
        $this->feature = $feature;

        $this->addEventListener(FormEvents::POST_SUBMIT, function (JobForm $form) {
            // call macro and flush collection items
            $this->data->tags->flush();
            $this->data->locations->flush();
            $this->data->features->flush();

            // deadline not exists in table "jobs" nor in fillable array. set value so model can transform it
            // to Carbon object
            $this->data->deadline = $form->get('deadline')->getValue();

            foreach ($form->get('tags')->getChildrenValues() as $tag) {
                $pivot = $this->data->tags()->newPivot(['priority' => $tag['priority']]);
                $model = (new Tag($tag))->setRelation('pivot', $pivot);

                $this->data->tags->add($model);
            }

            foreach ($form->get('features')->getChildrenValues() as $feature) {
                $checked = (int) $feature['checked'];

                $pivot = $this->data->features()->newPivot([
                    'checked'       => $checked,
                    'value'         => $checked ? ($feature['value'] ?? null) : null
                ]);

                $model = $this->feature->find($feature['id'])->setRelation('pivot', $pivot);

                $this->data->features->add($model);
            }

            $cities = (new City())->grab($form->get('city')->getValue());

            foreach ($cities as $city) {
                $this->data->locations->add(new Job\Location($this->geocode($city)));
            }

            $this->data->country()->associate((new Country())->find($form->get('country_id')->getValue()));
        });

        $this->addEventListener(FormEvents::PRE_RENDER, function (JobForm $form) {
            $session = $form->getRequest()->session();

            if ($session->hasOldInput('tags')) {
                $assoc = [];

                foreach ($form->get('tags')->getChildrenValues() as $tag) {
                    $assoc[] = [
                        'name' => $tag['name'],
                        'pivot' => [
                            'priority' => $tag['priority']
                        ]
                    ];
                }

                $form->get('tags')->setValue($assoc);
            }

            if ($session->hasOldInput('features')) {
                $assoc = [];

                foreach ($form->get('features')->getChildrenValues() as $feature) {
                    $assoc[] = [
                        'id' => $feature['id'],
                        'name' => $feature['name'],
                        'default' => $feature['default'],
                        'pivot' => [
                            'checked' => (int) $feature['checked'],
                            'value' => $feature['value'] ?? ''
                        ]
                    ];
                }

                $form->get('features')->setValue($assoc);
            }

            // tags as json (for vue.js)
            $form->get('tags')->setValue(collect($form->get('tags')->getChildrenValues())->toJson());
            // features as json (for vue.js)
            $form->get('features')->setValue(collect($form->get('features')->getChildrenValues())->toJson());
        });
    }

    public function buildForm()
    {
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
                    'class' => 'col-sm-9'
                ]
            ])
            ->add('seniority_id', 'select', [
                'rules' => 'integer',
                'label' => 'Staż pracy',
                'choices' => Job::getSeniorityList(),
                'empty_value' => '--',
                'row_attr' => [
                    'class' => 'col-sm-2'
                ]
            ])
            ->add('country_id', 'select', [
                'rules' => 'required|integer',
                'choices' => Country::getCountriesList()
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
                'choices' => Currency::getCurrenciesList(),
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
            ->add('features', 'collection', [
                'label' => 'Narzędzia oraz metodologia pracy',
                'help' => 'Zaznaczenie tych pól nie jest obowiązkowe, jednak wpływaja one na pozycję oferty na liście wyszukiwania.',
                'child_attr' => [
                    'type' => 'child_form',
                    'class' => FeaturesForm::class
                ]
            ])
            ->add('description', 'textarea', [
                'label' => 'Opis oferty (opcjonalnie)',
                'help' => 'Miejsce na szczegółowy opis oferty. Pole to jednak nie jest wymagane.',
                'style' => 'height: 140px',
                'row_attr' => [
                    'class' => 'form-group-border'
                ]
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
        // default attributes only if model does not exist and wasn't filled before.
        if (!$this->data->exists && !$this->data->isDirty(['title']) && !$this->isSubmitted()) {
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
}
