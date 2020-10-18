<?php

namespace Coyote\Http\Requests\Forum;

use Illuminate\Foundation\Http\FormRequest;

class PostCommentRequest extends FormRequest
{
    public function rules()
    {
        return [
            'text'          => 'required|string|max:580',
            'post_id'       => 'required|integer|exists:posts,id'
        ];
    }
}
