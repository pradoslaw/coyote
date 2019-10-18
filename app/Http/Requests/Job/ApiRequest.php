<?php

namespace Coyote\Http\Requests\Job;

use Coyote\Repositories\Contracts\CouponRepositoryInterface as CouponRepository;
use Coyote\Repositories\Contracts\CurrencyRepositoryInterface;
use Coyote\Repositories\Contracts\PlanRepositoryInterface as PlanRepository;
use Coyote\Rules\Base64Image;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->route('job') !== null) {
            return true;
        }

        $user = $this->user('api');

        /** @var \Coyote\Plan $plan */
        $plan = $this->plan()->findDefault($this->input('plan'));

        return $this->coupon()->findCoupon($user->id, $plan->gross_price) !== null;
    }

    /**
     * @throws AuthorizationException
     */
    protected function failedAuthorization()
    {
        throw new AuthorizationException('No sufficient funds to post this job offer.');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|string|min:2|max:60',
            'seniority' => ['nullable', 'string', Rule::in(['student', 'junior', 'mid', 'senior', 'lead', 'manager'])], // @todo przeniesc do slownika
            'is_remote' => 'bool',
            'remote_range' => 'integer|min:10|max:100',
            'salary_from' => 'nullable|integer|min:1',
            'salary_to' => 'nullable|integer|min:1',
            'rate' => ['nullable', 'string', Rule::in(['hourly', 'monthly', 'weekly', 'yearly'])],  // @todo przeniesc do slownika albo do stalej
            'is_gross' => 'boolean',
            'currency' => ['string', Rule::in($this->availableCurrencies())],
            'employment' => ['nullable', 'string', Rule::in(['mandatory', 'employment', 'b2b'])],
            'description' => 'string',
            'recruitment' => 'nullable|string',
            'email' => 'nullable|email',
            'plans' => [
                'bail',
                'string',
                Rule::in(array_map('strtolower', $this->plan()->where('is_active', 1)->pluck('name')->toArray()))
            ],
            'features.*.id' => 'required|int',
            'features.*.name' => 'string|max:100',
            'features.*.value' => 'nullable|string|max:100',
            'features.*.is_checked' => 'bool',
            'tags.*.name' => 'string|max:50|tag',
            'tags.*.priority' => 'int|min:0|max:2',
            'locations.*.city' => 'nullable|string|max:255',
            'locations.*.street' => 'nullable|string|max:255',
            'locations.*.street_number' => 'nullable|string|max:50',
            'locations.*.country' => 'nullable|string',
            'locations.*.latitude' => 'nullable|numeric',
            'locations.*.longitude' => 'nullable|numeric',

            'firm.name' => 'nullable|string|max:60',
            'firm.is_agency' => 'bool',
            'firm.website' => 'nullable|url',
            'firm.logo' => ['nullable', new Base64Image()],
            'firm.description' => 'nullable|string',
            'firm.employees' => 'nullable|integer',
            'firm.founded' => 'nullable|integer',
            'firm.youtube_url' => 'nullable|string|max:255|url|host:youtube.com,youtu.be',
            'firm.latitude' => 'nullable|numeric',
            'firm.longitude' => 'nullable|numeric',
            'firm.street' => 'nullable|string|max:255',
            'firm.city' => 'nullable|string|max:255',
            'firm.postcode' => 'nullable|string|max:50',
            'firm.house' => 'nullable|string|max:50',
        ];
    }

    private function plan(): PlanRepository
    {
        return $this->container[PlanRepository::class];
    }

    private function coupon(): CouponRepository
    {
        return $this->container[CouponRepository::class];
    }

    private function currency(): CurrencyRepositoryInterface
    {
        return $this->container[CurrencyRepositoryInterface::class];
    }

    private function availableCurrencies(): array
    {
        return $this->currency()->pluck('name');
    }
}
