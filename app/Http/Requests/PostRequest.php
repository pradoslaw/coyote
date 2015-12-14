<?php

namespace Coyote\Http\Requests;

use Coyote\Http\Requests\Request;

class PostRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $forum = $this->route('forum');
        return !(auth()->guest() && !$forum->enable_anonymous);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $forum = $this->route('forum');

        if ($forum->required_tag) {
            $rules['tag'] = 'required';
        }

        $rules = [
            'subject'           => 'required|min:3|max:200',
            'text'              => 'required'
        ];

        if (auth()->guest()) {
            $rules['user_name'] = 'required|unique:users,name';
        }

        return $rules;
    }
}
