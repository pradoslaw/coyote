<?php

namespace Coyote\Http\Requests;

class PostRequest extends Request
{
    const RULE_USER_NAME = 'sometimes|required|string|max:20|unique:users,name';
    const RULE_SUBJECT = 'sometimes|required|min:3|max:200';
    const RULE_TEXT = 'required';
    const RULE_STICKY = 'sometimes|bool';
    const RULE_TAGS = 'array';
    const RULE_POLL_TITLE = 'string';
    const RULE_POLL_ITEMS = 'required_with:title';
    // @todo dodac walidator sprawdzajacy ilosc (oraz dlugosc) linii
    const RULE_POLL_MAX_ITEMS = 'required_with:title|integer|min:1|max:20';
    const RULE_POLL_LENGTH = 'required_with:title|integer';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $forum = $this->route('forum');
        return !(auth()->guest() && !$forum->enable_anonymous) || !$forum->is_locked;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $forum = $this->route('forum');

        $rules = [
            'text' => self::RULE_TEXT,
            'user_name' => self::RULE_USER_NAME,
            'subject' => self::RULE_SUBJECT,
            'is_sticky' => self::RULE_STICKY,
            'tag' => self::RULE_TAGS,
            'title' => self::RULE_POLL_TITLE,
            'items' => self::RULE_POLL_ITEMS,
            'max_items' => self::RULE_POLL_MAX_ITEMS,
            'length' => self::RULE_POLL_LENGTH
        ];

        if ($forum->require_tag) {
            $rules['tag'] .= '|required';
        }

        // @todo fix in laravel 5.2
        if (is_array($this->request->get('tag'))) {
            foreach ($this->request->get('tag') as $key => $val) {
                $rules['tag.' . $key] = 'required|max:25|tag|tag_creation:2';
            }
        }

        return $rules;
    }
}
