<?php

namespace Coyote\Http\Forms\Job;

use Carbon\Carbon;
use Coyote\Country;
use Coyote\Currency;
use Coyote\Job;
use Coyote\Repositories\Contracts\CountryRepositoryInterface as CountryRepository;
use Coyote\Repositories\Contracts\FeatureRepositoryInterface as FeatureRepository;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\FormEvents;
use Coyote\Services\Geocoder\GeocoderInterface;
use Coyote\Services\Parser\Helpers\City;
use Coyote\Tag;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

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
     * @var CountryRepository
     */
    private $country;

    /**
     * @param GeocoderInterface $geocoder
     * @param FeatureRepository $feature
     * @param CountryRepository $country
     */
    public function __construct(GeocoderInterface $geocoder, FeatureRepository $feature, CountryRepository $country)
    {
        parent::__construct();

        $this->geocoder = $geocoder;
        $this->feature = $feature;
        $this->country = $country;

        $this->addEventListener(FormEvents::POST_SUBMIT, function (JobForm $form) {
            // call macro and flush collection items
            $this->data->tags->flush();
            $this->data->locations->flush();
            $this->data->features->flush();

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

            // set default deadline_at date time, only if offer was not publish yet.
            if (!$this->data->is_publish) {
                $this->data->plan_id = $form->get('plan_id')->getValue();

                $this->data->deadline_at = Carbon::now()->addDays($this->data->plan->length);
            }
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
                'rules' => 'nullable|integer',
                'label' => 'Staż pracy',
                'choices' => Job::getSeniorityList(),
                'empty_value' => '--',
                'row_attr' => [
                    'class' => 'col-sm-2'
                ]
            ])
            ->add('country_id', 'select', [
                'rules' => 'required|integer',
                'choices' => $this->country->pluck('name', 'id')
            ])
            ->add('city', 'text', [
                'rules' => 'nullable|string|city',
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
                'rules' => 'nullable|integer',
                'help' => 'Podanie tych informacji nie jest obowiązkowe, ale dzięki temu Twoja oferta zainteresuje więcej osób. Obiecujemy!',
                'attr' => [
                    'class' => 'input-inline'
                ]
            ])
            ->add('salary_to', 'text', [
                'rules' => 'nullable|integer',
                'attr' => [
                    'class' => 'input-inline'
                ]
            ])
            ->add('is_gross', 'select', [
                'rules' => 'required|boolean',
                'choices' => Job::getTaxList(),
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
                'rules' => 'required_if:enable_apply,0|nullable|string',
                'style' => 'height: 40px'
            ])
            ->add('email', 'email', [
                'label' => 'Email',
                'rules' => 'required_if:enable_apply,1|email',
                'help' => 'Adres e-mail nie będzie widoczny dla osób postronnych.'
            ])
            ->add('phone', 'tel', [
                'rules' => 'nullable|string|max:50',
                'label' => 'Numer telefonu',
                'help' => 'Wpisz swój numer telefonu, a wyślemy Ci powiadomienie o nadesłanej aplikacji.',
                'attr' => [
                    'placeholder' => 'Numer telefonu'
                ]
            ]);

        $this->setupPlanFields();
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
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        if ($validator->getMessageBag()->has('tags.*')) {
            $validator->getMessageBag()->add('tags', $validator->getMessageBag()->first('tags.*'));
        }

        parent::failedValidation($validator);
    }

    protected function setupPlanFields()
    {
        // can't show that fields if plan is enabled
        if ($this->data->is_publish) {
            return;
        }

        $this
            ->add('plan_id', 'hidden', [
                'rules' => [
                    'required',
                    'int',
                    Rule::exists('plans', 'id')->where('is_active', 1)
                ],
                'attr' => [
                    'id' => 'plan_id',
                    'v-model' => 'job.plan_id'
                ]
            ]);
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
