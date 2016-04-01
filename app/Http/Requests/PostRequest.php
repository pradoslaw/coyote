<?php

namespace Coyote\Http\Requests;

use Illuminate\Contracts\Validation\Validator;

class PostRequest extends Request
{
    const RULE_USER_NAME = 'sometimes|required|string|max:20|unique:users,name';
    const RULE_SUBJECT = 'sometimes|required|min:3|max:200';
    const RULE_TEXT = 'required';
    const RULE_STICKY = 'sometimes|bool';
    const RULE_TAGS = 'array';
    const RULE_TAG = 'max:25|tag|tag_creation:2';
    const RULE_POLL_TITLE = 'string';
    const RULE_POLL_ITEMS = 'required_with:title';
    // @todo dodac walidator sprawdzajacy ilosc (oraz dlugosc) linii
    const RULE_POLL_MAX_ITEMS = 'required_with:title|integer|min:1|max:20';
    const RULE_POLL_LENGTH = 'required_with:title|integer';
    const RULE_HUMAN = 'required';

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
        $forum  = $this->route('forum');
        $post   = $this->route('post');
        $topic  = $this->route('topic');

        $rules = [
            'text'          => self::RULE_TEXT,
            'user_name'     => self::RULE_USER_NAME,
            'subject'       => self::RULE_SUBJECT,
            'is_sticky'     => self::RULE_STICKY,
            'tags'          => self::RULE_TAGS,
            'tags.*'        => self::RULE_TAG,
            'title'         => self::RULE_POLL_TITLE,
            'items'         => self::RULE_POLL_ITEMS,
            'max_items'     => self::RULE_POLL_MAX_ITEMS,
            'length'        => self::RULE_POLL_LENGTH,
            'human'         => self::RULE_HUMAN
        ];

        // if I create new topic or edit first post ...
        if ((isset($post->id) && $post->id === $topic->first_post_id) || is_null($topic)) {
            if ($forum->require_tag) {
                $rules['tags'] .= '|required';
            }
        }

        return $rules;
    }

    /**
     * Format the errors from the given Validator instance.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return array
     */
    protected function formatErrors(Validator $validator)
    {
        $messages = $validator->errors();

        for ($i = 0; $i <= 5; $i++) {
            $error = $messages->first("tags.$i");

            if ($error) {
                $validator->errors()->add('tags', $error);
            }
        }

        return $validator->getMessageBag()->toArray();
    }
}
