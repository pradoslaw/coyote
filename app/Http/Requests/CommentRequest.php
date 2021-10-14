<?php

namespace Coyote\Http\Requests;

use Coyote\Comment;
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
        /** @var Comment $comment */
        $comment = $this->route('comment');
        $rule = Rule::requiredIf(!$comment->exists && !$this->input('parent_id'));

        return [
            'text' => 'required|string',
            'parent_id' => [
                'nullable',
                'int',
                Rule::exists('comments', 'id')->whereNull('parent_id')
            ],
            'resource_id' => [
                'bail',
                $rule,
                'nullable',
                'int'
            ],
            'resource_type' => [
                'bail',
                $rule,
                Rule::in([Guide::class, Job::class])
            ]
        ];
    }
}
