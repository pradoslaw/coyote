<?php

namespace Coyote\Http\Requests\Forum;

use Coyote\Forum;
use Coyote\Post;
use Coyote\Rules\TagDeleted;
use Coyote\Rules\MinWords;
use Coyote\Topic;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    const RULE_USER_NAME            = 'required|string|min:2|max:27';
    const RULE_USER_UNIQUE          = 'unique:users,name';
    const RULE_TEXT                 = 'required|spam_chinese:1|spam_foreign:1';
    const RULE_STICKY               = 'nullable|bool';
    const RULE_TAGS                 = 'array|max:5';
    const RULE_HUMAN                = 'required';

    /**
     * @var Post
     */
    protected $post;

    /**
     * @var Topic
     */
    protected $topic;

    /**
     * @var Forum
     */
    protected $forum;

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
        $this->post = $this->route('post') ?: new Post();
        $this->topic = $this->route('topic');
        $this->forum = $this->route('forum');

        $rules = [
            'text'          => self::RULE_TEXT
        ];

        if ($this->canChangeSubject()) {
            $require = Rule::requiredIf(fn () => count(array_filter($this->input('poll.items.*.text', []))) >= 2);

            $rules = array_merge($rules, [
                'subject'               => $this->subjectRule(),
                'tags'                  => self::RULE_TAGS,
                'tags.*'                => [
                    'bail',
                    'max:25',
                    'tag',
                    app(TagDeleted::class),
                    'tag_creation:300'
                ],

                'poll.items.*.text'     => [$require, 'nullable', 'string', 'max:200'],
                'poll.length'           => [$require, 'nullable', 'integer'],
                'poll.max_items'        => [$require, 'nullable', 'integer']
            ]);
        }

        if ($this->canMakeSticky()) {
            $rules['is_sticky'] = self::RULE_STICKY;
        }

        if ($this->canSeeUsername()) {
            $rules['user_name'] = [self::RULE_USER_NAME, $this->isUpdating() ? self::RULE_USER_UNIQUE : ''];
        }

        return $rules;
    }

    private function canChangeSubject(): bool
    {
        return !$this->topic->exists || ($this->topic->first_post_id === $this->post->id);
    }

    private function canMakeSticky(): bool
    {
        return $this->user() && $this->user()->can('sticky', $this->forum);
    }

    private function canSeeUsername(): bool
    {
        return $this->user() === null || (!empty($this->post->user_name) && $this->isUpdating());
    }

    private function isUpdating(): bool
    {
        return !empty($this->post->id);
    }

    protected function subjectRule()
    {
        return [
            'required',
            'min:3',
            'max:200',
            'spam_chinese:1',
            'not_regex:/^\[.+\]/',
            new MinWords()
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->sometimes('tags', 'required', function () {
            return $this->forum->require_tag && $this->canChangeSubject();
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

    public function messages()
    {
        return [
            'subject.not_regex' => 'Wygląda na to, że tytuł zawiera prefiks z nazwą tagu. Użyj dedykowanego pola z możliwością wpisania tagu.'
        ];
    }
}
