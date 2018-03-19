<?php

namespace Coyote\Http\Forms\Job;

use Coyote\Firm;
use Coyote\Industry;
use Coyote\Repositories\Contracts\CountryRepositoryInterface as CountryRepository;
use Coyote\Repositories\Contracts\IndustryRepositoryInterface as IndustryRepository;
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
     * @var IndustryRepository
     */
    protected $industry;

    /**
     * @var CountryRepository
     */
    protected $country;

    /**
     * It's public so we can use use attr from twig
     *
     * @var array
     */
    public $attr = [
        'method' => self::POST
    ];

    /**
     * @param IndustryRepository $industry
     * @param CountryRepository $country
     */
    public function __construct(IndustryRepository $industry, CountryRepository $country)
    {
        parent::__construct();

        $this->industry = $industry;
        $this->country = $country;

        $this->addEventListener(FormEvents::POST_SUBMIT, function (FirmForm $form) {
            if ($form->get('country')->getValue()) {
                $assoc = array_flip($this->country->pluck('name', 'id'));

                if (isset($assoc[$form->get('country')->getValue()])) {
                    // transform country name to country id
                    $form->get('country_id')->setValue($assoc[$form->get('country')->getValue()]);
                }
            }
        });

        $this->addEventListener(FormEvents::POST_SUBMIT, function (FirmForm $form) {
            $data = $form->all();
            $data['benefits'] = array_filter(array_unique(array_map('trim', $data['benefits'])));
            $data['youtube_url'] = $this->getEmbedUrl($form->get('youtube_url')->getValue());

            // if agency - set null value. we don't to show them with agencies offers
            if ($form->get('is_agency')->getValue()) {
                foreach (['employees', 'founded', 'headline', 'latitude', 'longitude', 'country_id', 'street', 'city', 'house', 'postcode'] as $column) {
                    $this->data->{$column} = null;
                }

                $data['benefits'] = [];
            }

            $models = [];

            foreach ($data['benefits'] as $benefit) {
                $models[] = new Firm\Benefit(['name' => $benefit]);
            }

            // call macro and replace collection items
            $this->data->benefits->replace($models);

            $models = [];

            foreach ($data['gallery'] as $photo) {
                if (!empty($photo)) {
                    $models[] = new Firm\Gallery(['file' => $photo]);
                }
            }

            // call macro and replace collection items
            $this->data->gallery->replace($models);

            $models = [];

            foreach ((array) $data['industries'] as $industry) {
                $models[] = new Industry(['id' => $industry]);
            }
            $this->data->industries->replace($models);
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
            ->add('industries', 'select', [
                'label' => 'Branża',
                'help' => 'Możesz wybrać jedną lub kilka branż w których działa firma.',
                'choices' => $this->industry->getAlphabeticalList(),
                'attr' => [
                    'id' => 'industries',
                    'multiple' => 'multiple'
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
            ->add('gallery', 'collection', [
                'label' => 'Dodaj zdjęcia',
                'child_attr' => [
                    'type' => 'child_form',
                    'class' => GalleryForm::class
                ]
            ])
            ->add('youtube_url', 'text', [
                'rules' => 'string|max:255|url|host:youtube.com,youtu.be',
                'label' => 'Nagranie wideo w Youtube',
                'help' => 'Film promujący firmę będzie wyświetlany pod ogłoszeniem o pracę.',
                'attr' => [
                    'v-model' => 'firm.youtube_url'
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
            ->add('country_id', 'hidden')
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

        $json['gallery'] = [];

        foreach ($this->get('gallery')->getChildrenValues() as $gallery) {
            if (!empty($gallery) && $gallery instanceof Firm\Gallery) {
                $json['gallery'][] = ['file' => $gallery->file, 'url' => (string) $gallery->photo->url()];
            }
        }

        $json['gallery'][] = ['file' => ''];

        return json_encode($json);
    }

    private function setDefaultOptions()
    {
        if ($this->data instanceof Firm && !$this->isSubmitted()) {
            $this->get('benefits')->setValue($this->data->benefits->all());
        }
    }

    /**
     * @param string $url
     * @return string
     */
    private function getEmbedUrl($url)
    {
        if (empty($url)) {
            return '';
        }

        $components = parse_url($url);

        if (empty($components['query'])) {
            return $url;
        }

        parse_str($components['query'], $query);

        return 'https://www.youtube.com/embed/' . $query['v'];
    }
}
