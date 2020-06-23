<?php

namespace Coyote\Http\Requests\Forum;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    const RULE_USER_NAME            = 'required|string|min:2|max:27';
    const RULE_USER_UNIQUE          = 'unique:users,name';
    const RULE_SUBJECT              = 'sometimes|required|min:3|max:200|spam_chinese:1';
    const RULE_TEXT                 = 'required|spam_chinese:1|spam_foreign:1';
    const RULE_STICKY               = 'nullable|bool';
    const RULE_SUBSCRIBE            = 'nullable|bool';
    const RULE_TAGS                 = 'array|max:5';
    const RULE_TAG                  = 'max:25|tag|tag_creation:50';
    const RULE_HUMAN                = 'required';
    const RULE_THROTTLE             = 'throttle';

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
        $post = $this->route('post');



        return [
            'subject'       => self::RULE_SUBJECT,
            'text'          => self::RULE_TEXT,
            'is_sticky'     => self::RULE_STICKY,
            'subscribe'     => self::RULE_SUBSCRIBE
        ];
    }


}
