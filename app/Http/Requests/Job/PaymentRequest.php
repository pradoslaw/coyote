<?php

namespace Coyote\Http\Requests\Job;

use Coyote\Repositories\Contracts\CountryRepositoryInterface as CountryRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Fluent;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @param CountryRepository $country
     * @return array
     */
    public function rules(CountryRepository $country)
    {
        $codes = $country->pluck('code', 'id');

        return [
            'payment_method' => 'required|in:card,p24',
            'price' => 'required|numeric',
            'coupon' => [
                'nullable',
                Rule::exists('coupons', 'code')->whereNull('deleted_at')
            ],

            'invoice.name' => 'bail|required_if:enable_invoice,true|nullable|string|max:200',
            'invoice.vat_id' => 'nullable|string|max:20',
            'invoice.address' => 'bail|required_if:enable_invoice,true|nullable|string|max:200',
            'invoice.city' => 'bail|required_if:enable_invoice,true|nullable|string|max:200',
            'invoice.postal_code' => 'bail|required_if:enable_invoice,true|nullable|string|max:30',
            'invoice.country_id' => ['bail', 'required_if:enable_invoice,true', 'nullable', Rule::in(array_flip($codes))]
        ];
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
//            'name' => 'nazwa',
//            'number' => 'numer karty kredytowej',
//            'exp' => 'data ważności karty',
//            'cvc' => 'CVC',
//            'payment_method' => 'forma płatności',
//            'card' => 'karta kredytowa',

            'invoice.name'      => 'nazwa',
            'invoice.vat_id'    => 'NIP'
        ];
    }

    /**
     * @return array
     */
//    public function messages()
//    {
//        return [
//            'transfer_method.required' => 'Przy tej formie płatności należy wybrać bank.',
//        ];
//    }

    /**
     * @return \Illuminate\Contracts\Validation\Validator
     */
//    protected function getValidatorInstance()
//    {
//        $validator = parent::getValidatorInstance();
//
//        $validator
////            ->sometimes(['name', 'number', 'cvc', 'exp'], 'required', function (Fluent $input) {
////                return $input->price > 0 && $input->payment_method == 'card';
////            })
//            ->sometimes('transfer_method', 'required', function (Fluent $input) {
//                return $input->price > 0 && $input->payment_method == 'transfer';
//            });
//
//        return $validator;
//    }
}
