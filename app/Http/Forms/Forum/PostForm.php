<?php

namespace Coyote\Http\Forms\Forum;

use Coyote\User;
use Illuminate\Http\Request;
use Coyote\Forum;
use Coyote\Post;
use Coyote\Services\FormBuilder\Form;
use Coyote\Topic;

class PostForm extends Form
{
    const RULE_USER_NAME            = 'sometimes|required|string|max:20|unique:users,name';
    const RULE_SUBJECT              = 'sometimes|required|min:3|max:200';
    const RULE_TEXT                 = 'required';
    const RULE_STICKY               = 'sometimes|bool';
    const RULE_TAGS                 = 'array|max:5';
    const RULE_TAG                  = 'max:25|tag|tag_creation:2';
    const RULE_POLL_TITLE           = 'string';
    const RULE_POLL_ITEMS           = 'required_with:title';
    // @todo dodac walidator sprawdzajacy ilosc (oraz dlugosc) linii
    const RULE_POLL_MAX_ITEMS       = 'required_with:title|integer|min:1|max:20';
    const RULE_POLL_LENGTH          = 'required_with:title|integer';
    const RULE_HUMAN                = 'required';
    const RULE_THROTTLE             = 'throttle'; // must be at the end

    protected $theme = 'forum.forms';

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
     * @var User|null
     */
    protected $user;

    /**
     * @var int|null
     */
    protected $userId;

    /**
     * Initialize models
     */
    public function __construct()
    {
        $this->post = new Post();
        $this->topic = new Topic();
        $this->forum = new Forum();
    }

    /**
     * @param array|mixed $data
     * @return $this
     */
    public function setData($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (!empty($value)) {
                    $this->$key = $value;
                }
            }
        }

        return parent::setData($data);
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        parent::setRequest($request);

        $this->user = $request->user();
        $this->userId = $this->user ? $this->user->id : null;

        return $this;
    }

    /**
     * Build form (add components and validation rules)
     */
    public function buildForm()
    {
        if (empty($this->topic) || $this->topic->first_post_id === $this->post->id) {
            $this->add('subject', 'text', [
                'rules' => self::RULE_SUBJECT,
                'label' => 'Temat',
                'value' => $this->topic->subject,
                'attr' => [
                    'tabindex' => 1,
                    'autofocus' => 'autofocus'
                ]
            ]);
        }

        if (!$this->userId || (empty($this->post->id) && !empty($this->post->user_name))) {
            $this->add('user_name', 'text', [
                'rules' => self::RULE_USER_NAME,
                'help' => 'Wpisz swoją nazwę użytkownika.',
                'label' => 'Nazwa użytkownika',
                'value' => $this->post->user_name,
                'attr' => [
                    'tabindex' => 2
                ]
            ]);
        }

        $this->add('text', 'textarea', [
            'rules' => self::RULE_TEXT,
            'attr' => [
                'tabindex' => 3,
                'data-paste-url' => route('forum.paste'),
                'data-prompt-url' => $this->topic->id ? route('forum.prompt', [$this->topic->id]) : route('user.prompt'),
            ],
            'template' => 'textarea',
            'value' => $this->post->text,
            'row_attr' => [
                'role' => 'tabpanel',
                'class' => 'tab-pane active',
                'id' => 'body'
            ]
        ]);

        if ($this->post->id === $this->topic->first_post_id) {
            $this->add('tags', 'text', [
                'rules' => self::RULE_TAGS,
                'label' => 'Tagi',
                'required' => $this->forum->require_tag,
                'help' => 'Możesz opisać swój wątek słowami kluczowymi - np. c#, .net (max. 5 tagów).',
                'template' => 'tags',
                'value' => $this->topic->getTagNames(),
                'attr' => [
                    'tabindex' => 4,
                    'id' => 'tags',
                    'placeholder' => $this->forum->require_tag ? 'Minimum 1 tag jest wymagany' : 'Np. c#, .net'
                ]
            ]);

            if ($this->userId && $this->user->can('sticky', $this->forum)) { // can sticky
                $this->add('is_sticky', 'checkbox', [
                    'rules' => self::RULE_STICKY,
                    'label' => 'Przyklejony'
                ]);
            }
        }

        if ($this->userId) {
            $this->add('subscribe', 'checkbox', [
                'rules' => 'boolean',
                'label' => 'Obserwuj wątek',
                'value' => 1,
                'checked' => $this->isPostSubscribed()
            ]);
        }

        $this->add('submit', 'submit', [
            'label' => 'Zapisz',
            'attr' => [
                'data-submit-state' => 'Zapisywanie...'
            ]
        ]);
    }

    /**
     * Determine if user subscribes this topic or not...
     *
     * @return bool|mixed
     */
    protected function isPostSubscribed()
    {
        if (!$this->userId) {
            return false;
        }

        if (!$this->topic->id) {
            $subscribe = $this->user->allow_subscribe;
        } else {
            $subscribe = $this->topic->subscribers()->forUser($this->userId)->exists();

            // we're creating new post...
            if ($this->post->id === null && $subscribe === false && $this->user->allow_subscribe) {
                // if this is the first post in this topic, subscribe option depends on user's default setting
                if ($this->topic->users()->forUser($this->userId)->exists()) {
                    $subscribe = true;
                }
            }
        }

        return $subscribe;
    }
}
