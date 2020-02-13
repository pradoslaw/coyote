<?php

namespace Coyote\Http\Forms\Job;

use Carbon\Carbon;
use Coyote\Services\FormBuilder\Form;
use Illuminate\Support\Fluent;
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
            ->add('payment_method', 'hidden', [
                'rules' => 'in:card,transfer',
                'value' => 'card',
                'attr' => [
                    'v-model' => 'form.payment_method'
                ]
            ])
            ->add('price', 'hidden', [
                'attr' => [
                    'v-model' => 'grossPrice'
                ]
            ])
            ->add('name', 'text', [
                'label' => 'Nazwa (jaka widnieje na karcie kredytowej)',
                'help' => 'Np. imię i nazwisko. Maksymalnie 32 znaki.',
                'rules' => 'bail|nullable|string|max:32',
                'attr' => [
                    'v-model' => 'form.name'
                ]
            ])
            ->add('number', 'text', [
                'label' => 'Numer karty kredytowej lub debetowej',
                'help' => 'Nie martw się. Numer karty nie będzie przechowywany na naszym serwerze.',
                'rules' => 'bail|nullable|string|cc_number',
                'attr' => [
                    'id' => 'credit-card',
                    'v-model' => 'form.number'
                ]
            ])
            ->add('exp_year', 'select', [
                'choices' => $this->getYearList(),
                'rules' => 'bail|int',
                'value' => date('Y'),
                'attr' => [
                    'class' => 'input-inline',
                    'v-model' => 'form.expiration_year'
                ]
            ])
            ->add('exp_month', 'select', [
                'choices' => $this->getMonthList(),
                'rules' => 'bail|int|cc_date:exp_month,exp_year',
                'value' => date('n'),
                'attr' => [
                    'class' => 'input-inline',
                    'v-model' => 'form.expiration_month'
                ]
            ])
            ->add('cvc', 'text', [
                'label' => 'Kod zabezpieczeń (CVC)',
                'help' => '3 ostatnie cyfry na odwrocie karty.',
                'rules' => 'bail|nullable|cc_cvc:number',
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
                'rules' => [
                    'nullable',
                    Rule::exists('coupons', 'code')->whereNull('deleted_at')
                ],
                'label' => 'Masz kod promocyjny?',
                'attr' => [
                    'class' => 'input-sm',
                    '@keyup' => 'validateCoupon',
                    'autocomplete' => 'off'
                ],
                'row_attr' => [
                    'v-show' => 'coupon.code || show_coupon === true',
                ]
            ])
            ->add('transfer_method', 'hidden', [
                'rules' => 'bail|int'
            ])
            ->add('invoice', 'child_form', [
                'class' => InvoiceForm::class,
                'value' => $this->prepareInvoiceData()
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
            'cvc' => 'CVC',
            'payment_method' => 'forma płatności',
            'card' => 'karta kredytowa'
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'transfer_method.required' => 'Przy tej formy płatności należy wybrać bank.',
        ];
    }

    /**
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();

        $validator
            ->sometimes(['name', 'number', 'cvc', 'exp_month'], 'required', function (Fluent $input) {
                return $input->price > 0 && $input->payment_method == 'card';
            })
            ->sometimes('transfer_method', 'required', function (Fluent $input) {
                return $input->price > 0 && $input->payment_method == 'transfer';
            });

        return $validator;
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

    /**
     * @return \Coyote\Invoice|\Coyote\Firm
     */
    private function prepareInvoiceData()
    {
        if ($this->data->job->user->invoices()->exists()) {
            // get the last user's invoice
            $invoice = $this->data->job->user->invoices()->orderBy('id', 'DESC')->first();

            if (empty($this->data->job->firm_id)
                || $this->data->job->firm_id && $this->data->job->firm->name === $invoice->name) {
                return $invoice;
            }
        }

        return $this->data->job->firm;
    }
}
