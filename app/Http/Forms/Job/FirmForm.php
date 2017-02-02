<?php

namespace Coyote\Http\Forms\Job;

use Coyote\Firm;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\FormEvents;
use Coyote\Services\Media\Factory as MediaFactory;

class FirmForm extends Form
{
    const PRE_JSON = 'pre_json';

    /**
     * @var string
     */
    protected $theme = self::THEME_INLINE;

    /**
     * @var \Coyote\Firm
     */
    protected $data;

    /**
     * It's public so we can use use attr from twig
     *
     * @var array
     */
    public $attr = [
        'method' => self::POST
    ];

    public function __construct()
    {
        parent::__construct();

        $this->addEventListener(FormEvents::POST_SUBMIT, function (FirmForm $form) {
            $this->forget($this->data->benefits);

            $data = $form->all();
            $data['benefits'] = array_filter(array_unique(array_map('trim', $data['benefits'])));

            // if agency - set null value. we don't to show them with agencies offers
            if ($form->get('is_agency')->getValue()) {
                foreach (['employees', 'founded', 'headline', 'latitude', 'longitude', 'street', 'city', 'house', 'postcode'] as $column) {
                    $this->data->{$column} = null;
                }

                $data['benefits'] = [];
            }

            foreach ($data['benefits'] as $benefit) {
                $this->data->benefits->add(new Firm\Benefit(['name' => $benefit]));
            }

            $this->data->fill($data);

            // new firm has empty ID.
            if (empty($data['id'])) {
                $this->data->exists = false;

                unset($this->data->id);
            } else {
                // assign firm id. id is not fillable - that's why we must set it directly.
                $this->data->id = (int) $data['id'];
            }
        });

        $this->addEventListener(self::PRE_JSON, function (Form $form) {
            if ($form->getRequest()->session()->hasOldInput('logo')) {
                $form->get('logo')->setValue(
                    $this->container[MediaFactory::class]->make('logo', ['file_name' => $form->getRequest()->session()->getOldInput('logo')])
                );
            }
        });
    }

    public function buildForm()
    {
        $this
            ->setAttr(['id' => 'firm-form', 'class' => 'submit-form', 'v-cloak' => 'v-cloak'])
            ->setUrl(route('job.submit.firm'))
            ->add('id', 'hidden', [
                'rules' => 'sometimes|integer',
                'attr' => [
                    'v-model' => 'firm.id'
                ]
            ])
            ->add('is_private', 'choice', [
                'multiple' => false,
                'rules' => 'boolean',
                'choices' => [
                    true => 'Jestem osobą prywatną',
                    false => 'Reprezentuje firmę'
                ],
                'child_attr' => [
                    'attr' => [
                        'v-model' => 'firm.is_private'
                    ]
                ]
            ])
            ->add('name', 'text', [
                'rules' => 'required_if:is_private,0|max:60',
                'label' => 'Nazwa firmy',
                'help' => 'Podając nazwę firmy, oferta staje się bardziej wiarygodna i wartościowa.',
                'attr' => [
                    'v-model' => 'firm.name',
                    '@keydown.once' => 'changeFirm'
                ]
            ])
            ->add('is_agency', 'choice', [
                'multiple' => false,
                'rules' => 'boolean',
                'choices' => [
                    0 => 'Bezpośredni pracodawca',
                    1 => 'Agencja pośrednictwa / IT outsourcing'
                ],
                'row_attr' => [
                    'class' => 'form-group-border'
                ],
                'child_attr' => [
                    'attr' => [
                        'v-model' => 'firm.is_agency'
                    ]
                ]
            ])
            ->add('website', 'text', [
                'rules' => 'sometimes|url',
                'label' => 'Strona WWW',
                'help' => 'Firmowa strona WWW. Będzie ona wyświetlana przy ofercie.',
                'row_attr' => [
                    'class' => 'form-group-border',
                    ':class' => "{'has-error': isInvalid(['website'])}"
                ],
                'attr' => [
                    'v-model' => 'firm.website'
                ]
            ])
            ->add('logo', 'hidden', [
                'rules' => 'string',
                'attr' => [
                    'v-model' => 'firm.logo'
                ]
            ])
            ->add('description', 'textarea', [
                'label' => 'Opis firmy',
                'rules' => 'string',
                'help' => 'Czym zajmuje się firma, w jakich branżach działa oraz jakie technologie wykorzystuje?',
                'attr' => [
                    'v-model' => 'firm.description'
                ]
            ])
            ->add('employees', 'select', [
                'label' => 'Liczba pracowników w firmie',
                'rules' => 'integer',
                'help' => 'Pozwala ocenić jak duża jest firma. Czy jest to korporacja, czy mała rodzinna firma?',
                'choices' => Firm::getEmployeesList(),
                'empty_value' => '--',
                'row_attr' => [
                    'class' => 'form-group-border',
                    'v-show' => 'firm.is_agency == 0'
                ],
                'attr' => [
                    'v-model' => 'firm.employees'
                ]
            ])
            ->add('founded', 'select', [
                'label' => 'Rok powstania',
                'help' => 'Pozwala ocenić jak duża jest firma. Czy jest to korporacja, czy mała rodzinna firma?',
                'rules' => 'integer',
                'choices' => Firm::getFoundedList(),
                'empty_value' => '--',
                'row_attr' => [
                    'class' => 'form-group-border',
                    ':class' => "{'has-error': isInvalid(['founded'])}",
                    'v-show' => 'firm.is_agency == 0'
                ],
                'attr' => [
                    'v-model' => 'firm.founded'
                ]
            ])
            ->add('headline', 'text', [
                'rules' => 'string|max:100',
                'label' => 'Motto lub nagłówek',
                'help' => 'Pozostało <strong>${ charCounter(\'firm.headline\', 100) }</strong> znaków',
                'attr' => [
                    'maxlength' => 100,
                    'v-model' => 'firm.headline'
                ],
                'row_attr' => [
                    ':class' => "{'has-error': isInvalid(['headline'])}",
                    'v-show' => 'firm.is_agency == 0'
                ]
            ])
            ->add('latitude', 'hidden', [
                'rules' => 'numeric',
                'attr' => [
                    'id' => 'latitude',
                    'v-model' => 'firm.latitude'
                ]
            ])
            ->add('longitude', 'hidden', [
                'rules' => 'numeric',
                'attr' => [
                    'id' => 'longitude',
                    'v-model' => 'firm.longitude'
                ]
            ])
            ->add('street', 'hidden', [
                'rules' => 'string|max:255',
                'attr' => [
                    'v-model' => 'firm.street'
                ]
            ])
            ->add('city', 'hidden', [
                'rules' => 'string|max:255',
                'attr' => [
                    'v-model' => 'firm.city'
                ]
            ])
            ->add('country', 'hidden', [
                'attr' => [
                    'v-model' => 'firm.country'
                ]
            ])
            ->add('postcode', 'hidden', [
                'rules' => 'string|max:50',
                'attr' => [
                    'v-model' => 'firm.postcode'
                ]
            ])
            ->add('house', 'hidden', [
                'rules' => 'string|max:50',
                'attr' => [
                    'v-model' => 'firm.house'
                ]
            ])
            ->add('address', 'text', [
                'label' => 'Adres',
                'help' => 'Wpisz adres i naciśnij Enter lub kliknij na mapę. Adres firmy będzie wyświetlany przy ofercie.',
                'attr' => [
                    'id' => 'address',
                    'v-model' => 'address',
                    '@keydown.enter.prevent' => 'changeAddress'
                ]
            ])
            ->add('benefits', 'collection', [
                'property' => 'name',
                'child_attr' => [
                    'type' => 'text'
                ]
            ])
            ->add('submit', 'submit', [
                'label' => 'Zapisz',
                'attr' => [
                    'data-submit-state' => 'Wysyłanie...'
                ]
            ]);

        $this->setDefaultOptions();
    }

    public function messages()
    {
        return ['name.required_if' => 'Nazwa firmy jest wymagana.'];
    }

    /**
     * @inheritdoc
     */
    public function toJson()
    {
        $this->events->dispatch(self::PRE_JSON);

        $json = json_decode(parent::toJson(), true);

        $json['thumbnail'] = null;
        $json['logo'] = null;

        if ($this->get('logo')->getValue()->getFilename()) {
            $json['thumbnail'] = (string) $this->get('logo')->getValue()->url();
            $json['logo'] = $this->get('logo')->getValue()->getFilename();
        }

        return json_encode($json);
    }

    private function setDefaultOptions()
    {
        if ($this->data instanceof Firm && !$this->isSubmitted()) {
            $this->get('benefits')->setValue($this->data->benefits->all());
        }
    }

    /**
     * @param Firm\Benefit[] $collection
     */
    private function forget($collection)
    {
        foreach ($collection as $key => $model) {
            unset($collection[$key]);
        }
    }
}
