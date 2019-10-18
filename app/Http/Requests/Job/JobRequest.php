<?php

namespace Coyote\Http\Requests\Job;

use Coyote\Job;
use Coyote\Repositories\Contracts\CurrencyRepositoryInterface;
use Coyote\Services\Job\Draft;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobRequest extends FormRequest
{
    const TITLE_RULE = 'required|string|min:2|max:60';
    const IS_REMOTE_RULE = 'bool';
    const REMOTE_RANGE_RULE = 'integer|min:10|max:100';
    const SALARY_FROM_RULE = 'nullable|integer|min:1';
    const SALARY_TO_RULE = 'nullable|integer|min:1|gt:salary_from';
    const IS_GROSS_RULE = 'boolean';

    const LOCATION_CITY = 'nullable|string|max:255';
    const LOCATION_STREET = 'nullable|string|max:255';
    const LOCATION_STREET_NUMBER = 'nullable|string|max:50';
    const LOCATION_COUNTRY = 'nullable|string';
    const LOCATION_LATITUDE = 'nullable|numeric';
    const LOCATION_LONGITUDE = 'nullable|numeric';

    const TAG_NAME_RULE = 'max:50|tag';
    const TAG_PRIORITY_RULE = 'required|int|min:0|max:2';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public static function seniorityRule()
    {
        return ['nullable', 'string', Rule::in([Job::STUDENT, Job::JUNIOR, Job::MID, Job::SENIOR, Job::LEAD, Job::MANAGER])];
    }

    public static function rateRule()
    {
        return ['nullable', 'string', Rule::in([Job::HOURLY, Job::MONTHLY, Job::WEEKLY, Job::YEARLY])];
    }

    public static function employmentRule()
    {
        return ['nullable', 'string', Rule::in([Job::MANDATORY, Job::EMPLOYMENT, Job::B2B])];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        /** @var Draft $draft */
        $draft = $this->container[Draft::class];
        $job = $draft->get(Job::class);

        return [
            'title' => self::TITLE_RULE,
            'seniority' => self::seniorityRule(),
            'is_remote' => self::IS_REMOTE_RULE,
            'remote_range' => self::REMOTE_RANGE_RULE,
            'salary_from' => self::SALARY_FROM_RULE,
            'salary_to' => self::SALARY_TO_RULE,
            'is_gross' => self::IS_GROSS_RULE,
            'currency_id' => ['required', 'int', 'exists:currencies,id'],
            'rate' => self::rateRule(),
            'employment' => self::employmentRule(),
            'recruitment' => 'required_if:enable_apply,false|nullable|string',
            'email' => 'required_if:enable_apply,true|email',
            'plan_id' => [
                'bail',
                Rule::requiredIf(function () use ($job) {
                    return ! $job->exists;
                }),

                'int',
                Rule::exists('plans', 'id')->where('is_active', 1),
            ],
            'features.*.id' => 'required|int',
            'features.*.name' => 'string|max:100',
            'features.*.value' => 'nullable|string|max:100',
            'features.*.is_checked' => 'bool',
            'tags.*.name' => self::TAG_NAME_RULE,
            'tags.*.priority' => self::TAG_PRIORITY_RULE,
            'locations.*.city' => self::LOCATION_CITY,
            'locations.*.street' => self::LOCATION_STREET,
            'locations.*.street_number' => self::LOCATION_STREET_NUMBER,
            'locations.*.country' => self::LOCATION_COUNTRY,
            'locations.*.latitude' => self::LOCATION_LATITUDE,
            'locations.*.longitude' => self::LOCATION_LONGITUDE
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if ($validator->getMessageBag()->has('tags.*')) {
            $validator->getMessageBag()->add('tags', $validator->getMessageBag()->first('tags.*'));
        }

        parent::failedValidation($validator);
    }
}
