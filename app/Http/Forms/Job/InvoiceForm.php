<?php

namespace Coyote\Http\Forms\Job;

use Coyote\Country;
use Coyote\Firm;
use Coyote\Services\FormBuilder\Form;

class InvoiceForm extends Form
{
    /**
     * @var string
     */
    protected $theme = self::THEME_INLINE;

    public function buildForm()
    {
        $this
            ->add('name', 'text', [
                'label' => 'Nazwa firmy',
                'rules' => 'string|max:200|required_with:enable_invoice'
            ])
            ->add('vat_id', 'text', [
                'label' => 'NIP (opcjonalnie)',
                'rules' => 'string|max:20'
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
                'choices' => $this->getCountriesCode(),
                'empty_value' => '--',
                'attr' => [
                    'class' => 'input-inline'
                ]
            ]);

        if (!empty($this->data) && !$this->isSubmitted() && $this->data instanceof Firm) {
            $this->get('address')->setValue($this->data->street . ' ' . $this->data->house);
            $this->get('country_id')->setValue($this->data->country_id);
        }
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => 'nazwa',
            'vat_id' => 'NIP'
        ];
    }

    /**
     * @return array
     */
    private function getCountriesCode()
    {
        return Country::pluck('code', 'id')->toArray();
    }
}
