<?php

namespace Coyote\Http\Requests\Job;

use Coyote\Repositories\Contracts\CountryRepositoryInterface as CountryRepository;
use Illuminate\Foundation\Http\FormRequest;
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

        $price = $this->input('price');
        $priceRule = Rule::requiredIf(fn () => $price > 0);

        return [
            'payment_method' => 'required|in:card,p24',
            'price' => 'required|numeric',
            'coupon' => [
                'nullable',
                Rule::exists('coupons', 'code')->whereNull('deleted_at')
            ],

            'invoice.name' => ['bail', $priceRule, 'nullable', 'string', 'max:200'],
            'invoice.vat_id' => 'nullable|string|max:20',
            'invoice.address' => ['bail', $priceRule, 'nullable', 'string', 'max:200'],
            'invoice.city' => ['bail', $priceRule, 'nullable', 'string', 'max:200'],
            'invoice.postal_code' => ['bail', $priceRule, 'nullable', 'string', 'max:30'],
            'invoice.country_id' => [
                'nullable',
                Rule::requiredIf($this->input('invoice.vat_id') !== null),
                Rule::in(array_flip($codes))
            ]
        ];
    }
}
