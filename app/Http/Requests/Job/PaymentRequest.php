<?php
namespace Coyote\Http\Requests\Job;

use Coyote\Repositories\Contracts\CountryRepositoryInterface as CountryRepository;
use Coyote\Rules\VatIdRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(CountryRepository $country): array
    {
        $codes = $country->pluck('code', 'id');
        $code = $codes[$this->input('invoice.country_id')] ?? '';

        $price = $this->input('price');
        $priceRule = Rule::requiredIf($price > 0);

        $rules = [
            'payment_method' => 'required|in:card,p24',
            'price'          => 'required|numeric',
            'coupon'         => [
                'nullable',
                Rule::exists('coupons', 'code')->whereNull('deleted_at'),
            ],
            'invoice.name'        => ['bail', $priceRule, 'nullable', 'string', 'max:200'],
            'invoice.vat_id'      => [
                'bail',
                'nullable',
                'string',
                'max:20',

            ],
            'invoice.address'     => ['bail', $priceRule, 'nullable', 'string', 'max:200'],
            'invoice.city'        => ['bail', $priceRule, 'nullable', 'string', 'max:200'],
            'invoice.postal_code' => ['bail', $priceRule, 'nullable', 'string', 'max:30'],
            'invoice.country_id'  => [
                'bail',
                $priceRule,
                'nullable',
                Rule::requiredIf($this->input('invoice.vat_id') !== null && $price > 0),
                Rule::in(array_flip($codes)),
            ],
        ];

        if ($price > 0) {
            $rules['invoice.vat_id'][] = new VatIdRule($code);
        }

        return $rules;
    }
}
