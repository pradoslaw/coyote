<?php

namespace Coyote\Http\Requests\Forum;

use Coyote\Forum;
use Coyote\Post;
use Coyote\Topic;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    const RULE_USER_NAME            = 'required|string|min:2|max:27';
    const RULE_USER_UNIQUE          = 'unique:users,name';
    const RULE_SUBJECT              = 'required|min:3|max:200|spam_chinese:1';
    const RULE_TEXT                 = 'required|spam_chinese:1|spam_foreign:1';
    const RULE_STICKY               = 'nullable|bool';
    const RULE_SUBSCRIBE            = 'nullable|bool';
    const RULE_TAGS                 = 'array|max:5';
    const RULE_TAG                  = 'max:25|tag|tag_creation:50';
    const RULE_HUMAN                = 'required';
    const RULE_THROTTLE             = 'throttle';

    const RULE_POLL_TITLE           = 'nullable|string|max:100';
    const RULE_POLL_ITEMS           = 'required_with:poll.title';
    const RULE_POLL_MAX_ITEMS       = 'required_with:poll.title|integer|min:1';
    const RULE_POLL_LENGTH          = 'required_with:poll.title|integer';

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
        /** @var Post $post */
        $post = $this->route('post') ?: new Post();
        /** @var Topic $topic */
        $topic = $this->route('topic');
        /** @var Forum $forum */
        $forum = $this->route('forum');

        $rules = [
            '_token'        => self::RULE_THROTTLE . ($post->id ? ":$post->id" : ''),
            'text'          => self::RULE_TEXT,
            'is_subscribed' => self::RULE_SUBSCRIBE
        ];

        if ($this->canChangeSubject($topic, $post)) {
            $rules = array_merge($rules, [
                'subject'       => self::RULE_SUBJECT,
                'tags'          => self::RULE_TAGS,
                'tags.*'        => self::RULE_TAG,

                'poll.title'    => self::RULE_POLL_TITLE,
                'poll.items'    => self::RULE_POLL_ITEMS,
                'poll.length'   => self::RULE_POLL_LENGTH,
                'poll.max_items'=> self::RULE_POLL_MAX_ITEMS
            ]);
        }

        if ($this->canMakeSticky($forum)) {
            $rules['is_sticky'] = self::RULE_STICKY;
        }

        if ($this->canSeeUsername($post)) {
            $rules['user_name'] = [self::RULE_USER_NAME, $this->isUpdating($post) ? self::RULE_USER_UNIQUE : ''];
        }

        return $rules;
    }

    private function canChangeSubject(Topic $topic, Post $post): bool
    {
        return !$topic->exists || ($topic->first_post_id === $post->id);
    }

    private function canMakeSticky(Forum $forum): bool
    {
        return $this->user() && $this->user()->can('sticky', $forum);
    }

    private function canSeeUsername(Post $post): bool
    {
        return $this->user() === null || (!empty($post->user_name) && $this->isUpdating($post));
    }

    private function isUpdating(Post $post): bool
    {
        return !empty($post->id);
    }

    public function withValidator(Validator $validator)
    {
        $validator->sometimes('tags', 'required', function () {
            /** @var Forum $forum */
            $forum = $this->route('forum');

            return $forum->require_tag;
        });

        $validator->after(function ($validator) {
            $messages = $validator->errors();

            for ($i = 0; $i <= 5; $i++) {
                $error = $messages->first("tags.$i");

                if ($error) {
                    $validator->errors()->add('tags', $error);
                }
            }
        });
    }
}
