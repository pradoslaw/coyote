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
            'title' => JobRequest::TITLE,
            'seniority' => JobRequest::seniorityRule(),
            'is_remote' => JobRequest::IS_REMOTE,
            'remote_range' => JobRequest::REMOTE_RANGE,
            'salary_from' => JobRequest::SALARY_FROM,
            'salary_to' => JobRequest::SALARY_TO,
            'rate' => JobRequest::rateRule(),
            'is_gross' => JobRequest::IS_GROSS,
            'currency' => 'nullable|string|exists:currencies,name',
            'employment' => JobRequest::employmentRule(),
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
            'tags.*.name' => JobRequest::TAG_NAME,
            'tags.*.priority' => JobRequest::TAG_PRIORITY,
            'locations.*.city' => JobRequest::LOCATION_CITY,
            'locations.*.street' => JobRequest::LOCATION_STREET,
            'locations.*.street_number' => JobRequest::LOCATION_STREET_NUMBER,
            'locations.*.country' => JobRequest::LOCATION_COUNTRY,
            'locations.*.latitude' => JobRequest::LOCATION_LATITUDE,
            'locations.*.longitude' => JobRequest::LOCATION_LONGITUDE,

            'firm.name' => 'nullable|string|max:60',
            'firm.is_agency' => FirmRequest::IS_AGENCY,
            'firm.website' => FirmRequest::WEBSITE,
            'firm.logo' => ['nullable', new Base64Image()],
            'firm.description' => FirmRequest::DESCRIPTION,
            'firm.employees' => 'nullable|integer',
            'firm.founded' => 'nullable|integer',
            'firm.youtube_url' => FirmRequest::YOUTUBE_URL,
            'firm.latitude' => JobRequest::LOCATION_LATITUDE,
            'firm.longitude' => JobRequest::LOCATION_LONGITUDE,
            'firm.street' => JobRequest::LOCATION_STREET,
            'firm.city' => JobRequest::LOCATION_CITY,
            'firm.postcode' => 'nullable|string|max:50',
            'firm.street_number' => JobRequest::LOCATION_STREET_NUMBER,
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
        return app(CurrencyRepositoryInterface::class);
    }

    private function availableCurrencies(): array
    {
        return self::currency()->pluck('name');
    }
}
