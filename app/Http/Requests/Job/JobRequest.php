<?php

namespace Coyote\Http\Requests\Job;

use Coyote\Job;
use Coyote\Services\Job\Draft;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobRequest extends FormRequest
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
        /** @var Draft $draft */
        $draft = $this->container[Draft::class];
        $job = $draft->get(Job::class);

        return [
            'text' => 'min:2|max:60',
            'seniority_id' => 'nullable|integer',
//            'country_id' => 'required|integer',
            'is_remote' => 'bool',
            'remote_range' => 'integer|min:10|max:100',
            'salary_from' => 'nullable|integer',
            'salary_to' => 'nullable|integer',
            'is_gross' => 'required|boolean',
            'currency_id' => 'required|integer',
            'rate_id' => 'required|integer',
            'employment_id' => 'required|integer',
            'recruitment' => 'required_if:enable_apply,0|nullable|string',
            'email' => 'required_if:enable_apply,1|email',
            'plan_id' => [
                'bail',
                Rule::requiredIf(function () use ($job) {
                    return ! $job->exists;
                }),

                'int',
                Rule::exists('plans', 'id')->where('is_active', 1),
            ]
        ];
    }
}
