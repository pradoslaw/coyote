<?php

namespace Coyote\Http\Forms\Job;

use Coyote\Firm;
use Coyote\Repositories\Contracts\CountryRepositoryInterface as CountryRepository;
use Coyote\Services\FormBuilder\Form;

class InvoiceForm extends Form
{
    const DEFAULT_COUNTRY = 'PL';

    /**
     * @var string
     */
    protected $theme = self::THEME_INLINE;

    /**
     * @var CountryRepository
     */
    protected $country;

    /**
     * @param CountryRepository $country
     */
    public function __construct(CountryRepository $country)
    {
        parent::__construct();

        $this->country = $country;
    }

    public function buildForm()
    {
        $codeList = $this->getCountriesCode();

        $this
            ->add('name', 'text', [
                'label' => 'Nazwa firmy',
                'rules' => 'string|max:200|required_with:enable_invoice'
            ])
            ->add('vat_id', 'text', [
                'label' => 'NIP (opcjonalnie)',
                'rules' => 'string|max:20',
                'attr' => [
                    '@keydown' => 'calculate',
                    'v-model' => 'form.invoice.vat_id'
                ]
            ])
            ->add('address', 'text', [
                'rules' => 'string|required_with:enable_invoice|max:200',
                'label' => 'Adres',
            ])
            ->add('city', 'text', [
                'rules' => 'string|required_with:enable_invoice|max:200',
                'label' => 'Miejscowość',
            ])
            ->add('postal_code', 'text', [
                'rules' => 'string|required_with:enable_invoice|max:30',
                'label' => 'Kod pocztowy',
            ])
            ->add('country_id', 'select', [
                'choices' => $codeList,
                'attr' => [
                    'class' => 'input-inline',
                    '@change' => 'calculate',
                    'v-model' => 'form.invoice.country_id'
                ]
            ]);

        if (!$this->isSubmitted()) {
            if ($this->data instanceof Firm) {
                $this->get('address')->setValue($this->data->street . ' ' . $this->data->house);
            }

            $this->get('country_id')->setValue($this->data->country_id ?? $this->getDefaultCode($codeList));
        }
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            'name'      => 'nazwa',
            'vat_id'    => 'NIP'
        ];
    }

    /**
     * @return array
     */
    private function getCountriesCode()
    {
        return $this->country->pluck('code', 'id');
    }

    /**
     * @param array $codeList
     * @return string
     */
    private function getDefaultCode($codeList)
    {
        $geoIp = $this->container->make('geo-ip');
        $result = $geoIp->ip($this->request->ip());

        $codeList = array_flip($codeList);

        if (!isset($result->country_code)) {
            return $codeList[self::DEFAULT_COUNTRY];
        }

        return $codeList[$result->country_code] ?? self::DEFAULT_COUNTRY;
    }
}
