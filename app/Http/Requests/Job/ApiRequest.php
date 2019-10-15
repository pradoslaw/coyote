<?php

namespace Coyote\Http\Requests\Job;

use Coyote\Repositories\Contracts\CouponRepositoryInterface;
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
            'title' => 'required|string|min:2|max:60',
            'seniority_id' => 'nullable|integer',
            'is_remote' => 'bool',
            'remote_range' => 'integer|min:10|max:100',
            'salary_from' => 'nullable|integer',
            'salary_to' => 'nullable|integer',
            'is_gross' => 'boolean',
            'currency_id' => 'integer',
            'rate_id' => 'integer',
            'employment_id' => 'integer',
            'description' => 'string',
            'recruitment' => 'nullable|string',
            'email' => 'nullable|email',
            'plan_id' => [
                'bail',
                'int',
                Rule::exists('plans', 'id')->where('is_active', 1),
            ],
            'features.*.id' => 'required|int',
            'features.*.name' => 'string|max:100',
            'features.*.value' => 'nullable|string|max:100',
            'features.*.is_checked' => 'bool',
            'tags.*.name' => 'string|max:50|tag',
            'tags.*.priority' => 'required|int|min:0|max:2',
            'locations.*.city' => 'nullable|string|max:255',
            'locations.*.address' => 'nullable|string|max:255',
            'locations.*.country' => 'nullable|string',
            'locations.*.latitude' => 'nullable|numeric',
            'locations.*.longitude' => 'nullable|numeric',

            'firm.name' => 'nullable|string|max:60',
            'firm.is_agency' => 'bool',
            'firm.website' => 'nullable|url',
            'firm.logo' => 'nullable|url',
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

    protected function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();

        $validator->after(function () {
            $job = $this->route('job');

            if ($job !== null) {
                return true;
            }

            $user = $this->user('api');
            $coupon = $this->container[CouponRepositoryInterface::class];
var_dump($coupon->where('user_id', $user->id)->count());exit;
            return $coupon->where('user_id', $user->id)->count() > 0;
        });

        return $validator;
    }
}
