<?php

namespace Coyote\Http\Requests\Job;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'text' => 'required|string',
            'email' => 'sometimes|email',
            'job_id' => 'int|exists:jobs,id',
            'parent_id' => 'sometimes|int|exists:job_comments,id'
        ];
    }
}
