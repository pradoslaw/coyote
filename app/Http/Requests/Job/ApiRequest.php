<?php

namespace Coyote\Http\Requests\Job;

use Coyote\Repositories\Contracts\CouponRepositoryInterface as CouponRepository;
use Coyote\Repositories\Contracts\PlanRepositoryInterface as PlanRepository;
use Coyote\Rules\Base64Image;
use Coyote\User;

class ApiRequest extends JobRequest
{
    private User $user;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->user = $this->user('api');

        if ($this->route('job') !== null) {
            return $this->user->can('update', $this->route('job'));
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => self::TITLE,
            'seniority' => $this->seniorityRule(),
            'is_remote' => self::IS_REMOTE,
            'remote_range' => self::REMOTE_RANGE,
            'salary_from' => self::SALARY_FROM,
            'salary_to' => self::SALARY_TO,
            'rate' => $this->rateRule(),
            'is_gross' => self::IS_GROSS,
            'currency' => 'nullable|string|exists:currencies,name',
            'employment' => $this->employmentRule(),
            'description' => 'string',
            'recruitment' => 'nullable|string',
            'email' => 'nullable|email',
            'features.*.id' => 'required|int',
            'features.*.name' => 'string|max:100',
            'features.*.value' => 'nullable|string|max:100',
            'features.*.is_checked' => 'bool',
            'tags.*.name' => self::TAG_NAME,
            'tags.*.priority' => self::TAG_PRIORITY,
            'locations.*.city' => self::LOCATION_CITY,
            'locations.*.street' => self::LOCATION_STREET,
            'locations.*.street_number' => self::LOCATION_STREET_NUMBER,
            'locations.*.country' => self::LOCATION_COUNTRY,
            'locations.*.latitude' => self::LOCATION_LATITUDE,
            'locations.*.longitude' => self::LOCATION_LONGITUDE,

            'firm.name' => 'nullable|string|max:60',
            'firm.is_agency' => self::IS_AGENCY,
            'firm.website' => self::WEBSITE,
            'firm.logo' => ['nullable', new Base64Image()],
            'firm.description' => self::DESCRIPTION,
            'firm.employees' => 'nullable|integer',
            'firm.founded' => 'nullable|integer',
            'firm.youtube_url' => self::YOUTUBE_URL,
            'firm.latitude' => self::LOCATION_LATITUDE,
            'firm.longitude' => self::LOCATION_LONGITUDE,
            'firm.street' => self::LOCATION_STREET,
            'firm.city' => self::LOCATION_CITY,
            'firm.postcode' => 'nullable|string|max:50',
            'firm.street_number' => self::LOCATION_STREET_NUMBER,
        ];
    }

    protected function getValidatorInstance()
    {
        return parent::getValidatorInstance()->after(function ($validator) {
            if ($this->route('job') !== null) {
                return true;
            }

            /** @var \Coyote\Plan $plan */
            $plan = $this->plan()->findDefault($this->input('plan'));

            if (!$plan) {
                $validator->errors()->add('plan', 'Invalid plan name.');

                return false;
            }

            if ($plan->price > 0) {
                if ($this->coupon()->findCoupon($this->user->id, $plan->price) === null) {
                    $validator->errors()->add('plan', 'No sufficient funds to post this job offer.');

                    return false;
                }
            }

            return true;
        });
    }

    private function plan(): PlanRepository
    {
        return $this->container[PlanRepository::class];
    }

    private function coupon(): CouponRepository
    {
        return $this->container[CouponRepository::class];
    }
}
