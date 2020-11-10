<?php

namespace Coyote\Http\Requests\Forum;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostCommentRequest extends FormRequest
{
    public function rules()
    {
        return [
            'text'          => 'required|string|max:580',
            'post_id'       => ['required', 'integer', Rule::exists('posts', 'id')->whereNull('deleted_at')]
        ];
    }
}
