<?php

namespace Coyote\Http\Requests\Job;

use Coyote\Job;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobRequest extends FormRequest
{
    const TITLE = 'required|string|min:2|max:60';
    const IS_REMOTE = 'bool';
    const REMOTE_RANGE = 'integer|min:10|max:100';
    const SALARY_FROM = 'nullable|integer|min:1';
    const SALARY_TO = 'nullable|integer|min:1';
    const IS_GROSS = 'boolean';

    const LOCATION_CITY = 'nullable|string|max:255';
    const LOCATION_STREET = 'nullable|string|max:255';
    const LOCATION_STREET_NUMBER = 'nullable|string|max:50';
    const LOCATION_COUNTRY = 'nullable|string';
    const LOCATION_LATITUDE = 'nullable|numeric';
    const LOCATION_LONGITUDE = 'nullable|numeric';

    const TAG_NAME = 'max:50|tag';
    const TAG_PRIORITY = 'nullable|int|min:0|max:2';

    const IS_AGENCY = 'bool';
    const WEBSITE = 'nullable|url';
    const DESCRIPTION = 'nullable|string';
    const YOUTUBE_URL = 'nullable|string|max:255|url|host:youtube.com,youtu.be';

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
        return ['nullable', 'string', Rule::in([Job::MANDATORY, Job::EMPLOYMENT, Job::B2B, Job::CONTRACT])];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $job = $this->route('job') ?: new Job();



        return [
            'title' => self::TITLE,
            'seniority' => self::seniorityRule(),
            'is_remote' => self::IS_REMOTE,
            'remote_range' => self::REMOTE_RANGE,
            'salary_from' => self::SALARY_FROM,
            'salary_to' => self::SALARY_TO,
            'is_gross' => self::IS_GROSS,
            'currency_id' => ['required', 'int', 'exists:currencies,id'],
            'rate' => self::rateRule(),
            'employment' => self::employmentRule(),
            'recruitment' => [
                Rule::requiredIf(function () use ($job) {
                    return $this->input('enable_apply') === false;
                }),
                'nullable',
                'string'
            ],
            'email' => [
                'bail',
                Rule::requiredIf($this->input('enable_apply') === true),
                'nullable',
                'email'
            ],
            'plan_id' => [
                'bail',
                Rule::requiredIf(function () use ($job) {
                    return ! $job->exists;
                }),
                'int',
                Rule::exists('plans', 'id')->where('is_active', 1),
            ],
            'firm_id' => [
                'nullable',
                Rule::exists('firms', 'id')->whereNull('deleted_at')
            ],
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

            'firm.id' => [
                'nullable',
                'integer',
                Rule::exists('firms', 'id')->whereNull('deleted_at')
            ],
            'firm.name' => 'required_with:job.firm_id|max:60',
            'firm.is_agency' => self::IS_AGENCY,
            'firm.website' => self::WEBSITE,
            'firm.logo' => 'nullable|string',
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

    protected function failedValidation(Validator $validator)
    {
        if ($validator->getMessageBag()->has('tags.*')) {
            $validator->getMessageBag()->add('tags', $validator->getMessageBag()->first('tags.*'));
        }

        parent::failedValidation($validator);
    }
}
