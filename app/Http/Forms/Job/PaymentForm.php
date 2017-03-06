<?php

namespace Coyote\Http\Forms\Job;

use Carbon\Carbon;
use Coyote\Services\FormBuilder\Form;

class PaymentForm extends Form
{
    /**
     * @var string
     */
    protected $theme = self::THEME_INLINE;

    public function buildForm()
    {
        $this
            ->setAttr(['class' => 'submit-form'])
            ->add('name', 'text', [
                'required' => true,
                'label' => 'Nazwa (jaka widnieje na karcie kredytowej)',
                'help' => 'Np. imię i nazwisko. Maksymalnie 32 znaki.',
                'rules' => 'string|max:32'
            ])
            ->add('number', 'text', [
                'required' => true,
                'label' => 'Numer karty kredytowej lub debetowej',
                'help' => 'Nie martw się. Numer karty nie będzie przechowywany na naszym serwerze.',
                'rules' => 'string'
            ])
            ->add('exp_year', 'select', [
                'required' => true,
                'rules' => 'int',
                'choices' => $this->getYearList(),
                'attr' => [
                    'class' => 'input-inline'
                ]
            ])
            ->add('exp_month', 'select', [
                'required' => true,
                'rules' => 'int',
                'choices' => $this->getMonthList(),
                'attr' => [
                    'class' => 'input-inline'
                ]
            ])
            ->add('cvc', 'text', [
                'required' => true,
                'rules' => 'int',
                'label' => 'Kod zabezpieczeń (CVC)',
                'help' => '3 ostatnie cyfry na odwrocie karty.'
            ])
            ->add('enable_invoice', 'checkbox', [
                'label' => 'Tak, chcę otrzymać fakturę',
                'value' => true,
                'attr' => [
                    'id' => 'enable-invoice'
                ]
            ])
            ->add('invoice', 'child_form', [
                'class' => InvoiceForm::class,
                'value' => $this->data->job->firm
            ]);
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => 'nazwa',
            'number' => 'numer karty kredytowej',
            'cvc' => 'CVC'
        ];
    }

    /**
     * @return array
     */
    private function getYearList()
    {
        $yearList = [];
        $currYear = date('Y');

        for ($i = $currYear; $i <= $currYear + 10; $i++) {
            $yearList[$i] = $i;
        }

        return $yearList;
    }

    /**
     * @return array
     */
    private function getMonthList()
    {
        $monthList = [];
        $currYear = date('Y');

        for ($i = 1; $i <= 12; $i++) {
            $monthList[$i] = sprintf('%2d - %s', $i, Carbon::createFromDate($currYear, $i, 1)->formatLocalized('%B'));
        }

        return $monthList;
    }
}
