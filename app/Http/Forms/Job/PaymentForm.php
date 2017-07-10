<?php

namespace Coyote\Http\Forms\Job;

use Carbon\Carbon;
use Coyote\Services\FormBuilder\Form;
use Illuminate\Validation\Rule;

class PaymentForm extends Form
{
    /**
     * @var string
     */
    protected $theme = self::THEME_INLINE;

    public function buildForm()
    {
        $this
            ->setAttr([
                'class' => 'submit-form',
                'id' => 'payment-form',
                '@submit.prevent' => 'submit'
            ])
            ->add('name', 'text', [
                'required' => true,
                'label' => 'Nazwa (jaka widnieje na karcie kredytowej)',
                'help' => 'Np. imię i nazwisko. Maksymalnie 32 znaki.',
                'rules' => 'string|max:32',
                'attr' => [
                    'v-model' => 'form.name'
                ]
            ])
            ->add('number', 'text', [
                'required' => true,
                'label' => 'Numer karty kredytowej lub debetowej',
                'help' => 'Nie martw się. Numer karty nie będzie przechowywany na naszym serwerze.',
                'rules' => 'string|cc_number',
                'attr' => [
                    'id' => 'credit-card',
                    'v-model' => 'form.number'
                ]
            ])
            ->add('exp_year', 'select', [
                'choices' => $this->getYearList(),
                'rules' => 'int',
                'value' => date('Y'),
                'attr' => [
                    'class' => 'input-inline',
                    'v-model' => 'form.expiration_year'
                ]
            ])
            ->add('exp_month', 'select', [
                'choices' => $this->getMonthList(),
                'rules' => 'int|cc_date:exp_month,exp_year',
                'value' => date('n'),
                'attr' => [
                    'class' => 'input-inline',
                    'v-model' => 'form.expiration_month'
                ]
            ])
            ->add('cvc', 'text', [
                'label' => 'Kod zabezpieczeń (CVC)',
                'help' => '3 ostatnie cyfry na odwrocie karty.',
                'rules' => 'required|cc_cvc:number',
                'attr' => [
                    'id' => 'cvc',
                    'v-model' => 'form.cvc'
                ]
            ])
            ->add('enable_invoice', 'checkbox', [
                'label' => 'Tak, chcę otrzymać fakturę',
                'value' => true,
                'attr' => [
                    'id' => 'enable-invoice'
                ]
            ])
            ->add('coupon', 'text', [
                'rules' => Rule::exists('coupons', 'code')->whereNull('deleted_at'),
                'label' => 'Masz kod promocyjny?',
                'attr' => [
                    'class' => 'input-sm',
                    '@keyup' => 'validateCoupon'
                ],
                'row_attr' => [
                    'v-show' => 'coupon.code || show_coupon === true'
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
