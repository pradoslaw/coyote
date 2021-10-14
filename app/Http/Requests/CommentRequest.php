<?php

namespace Coyote\Http\Requests;

use Coyote\Job;
use Coyote\Guide;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommentRequest extends FormRequest
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
        $comment = $this->route('comment');
        $requireResource = Rule::requiredIf(fn () => $comment === null);

        return [
            'text' => 'required|string',
            'parent_id' => [
                'nullable',
                'int',
                Rule::exists('comments', 'id')->whereNull('parent_id')
            ],
            'resource_id' => [
                $requireResource,
                'int'
            ],
            'resource_type' => [
                $requireResource,
                Rule::in([Guide::class, Job::class])
            ]
        ];
    }
}
