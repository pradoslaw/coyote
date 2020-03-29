<?php

namespace Coyote\Http\Forms\Forum;

use Coyote\Events\PostSubmitting;
use Coyote\Http\Forms\AttachmentForm;
use Coyote\Poll;
use Coyote\Repositories\Contracts\Post\AttachmentRepositoryInterface;
use Coyote\Services\FormBuilder\FormEvents;
use Coyote\User;
use Coyote\Forum;
use Coyote\Post;
use Coyote\Services\FormBuilder\Form;
use Coyote\Topic;
use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Collection;

class PostForm extends Form
{
    const RULE_USER_NAME            = 'required|string|min:2|max:27';
    const RULE_USER_UNIQUE          = 'unique:users,name';
    const RULE_SUBJECT              = 'sometimes|required|min:3|max:200|spam_chinese:1';
    const RULE_TEXT                 = 'required|spam_chinese:1|spam_foreign:1';
    const RULE_STICKY               = 'nullable|bool';
    const RULE_TAGS                 = 'array|max:5';
    const RULE_TAG                  = 'max:25|tag|tag_creation:50';
    const RULE_HUMAN                = 'required';
    const RULE_THROTTLE             = 'throttle';

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
        parent::__construct();

        $this->post = new Post();
        $this->topic = new Topic();
        $this->forum = new Forum();

        $this->addEventListener(FormEvents::PRE_RENDER, function (Form $form) {
            $session = $form->getRequest()->session();

            if ($session->hasOldInput('attachments')) {
                $repository = app(AttachmentRepositoryInterface::class);

                $oldInput = $session->getOldInput('attachments');
                array_pluck($oldInput, 'file');

                $form->get('attachments')->setValue($repository->findMany($oldInput));
            }

            if ($form->get('poll') !== null) {
                $value = $form->get('poll')->get('items')->getValue();

                if ($value instanceof Collection) {
                    $form->get('poll')->get('items')->setValue(
                        $value->implode('text', "\n")
                    );
                }
            }
        });

        $this->addEventListener(FormEvents::PRE_SUBMIT, function (Form $form) {
            PostSubmitting::dispatch($form);
        });
    }

    /**
     * @inheritdoc
     */
    protected function setupRules()
    {
        parent::setupRules();

        $this->request->merge(array_filter(array_map('trim', $this->request->only('subject', 'user_name', 'text'))));
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rule = self::RULE_THROTTLE;
        if (!empty($this->post->id)) {
            $rule .= ':' . $this->post->id;
        }

        return parent::rules() + ['_token' => $rule];
    }

    /**
     * @param array|mixed $data
     * @param bool $rebuildForm
     * @return \Coyote\Services\FormBuilder\Form
     */
    public function setData($data, $rebuildForm = true)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (!empty($value)) {
                    $this->{$key} = $value;
                }
            }
        }

        return parent::setData($data, $rebuildForm);
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

            $poll = $this->topic->poll()->getResults();
            if (!$poll) {
                $poll = $this->container->make(Poll::class);
            }

            $this->add('poll', 'child_form', [
                'class' => PollForm::class,
                'template' => 'poll_form',
                'value' => $poll
            ]);

            $this->add('tags', 'collection', [
                'rules' => self::RULE_TAGS,
                'label' => 'Tagi',
                'required' => $this->forum->require_tag,
                'help' => 'Możesz opisać swój wątek słowami kluczowymi - np. c#, .net (max. 5 tagów).',
                'template' => 'tags',
                'value' => $this->topic->tags()->get(),
                'attr' => [
                    'tabindex' => 4,
                    'id' => 'tags',
                    'class' => 'form-control',
                    'placeholder' => $this->forum->require_tag ? 'Minimum 1 tag jest wymagany' : 'Np. c#, .net'
                ],
                'property' => 'name',
                'child_attr' => [
                    'type' => 'hidden',
                    'rules' => self::RULE_TAG
                ],

            ]);

            if ($this->userId !== null && $this->user->can('sticky', $this->forum)) { // can sticky
                $this->add('is_sticky', 'checkbox', [
                    'rules' => self::RULE_STICKY,
                    'label' => 'Przyklejony',
                    'value' => (int) $this->topic->is_sticky
                ]);
            }
        }

        if ($this->canSeeUsername()) {
            $this->add('user_name', 'text', [
                'rules' => self::RULE_USER_NAME . (!$this->isUpdating() ? '|' . self::RULE_USER_UNIQUE : ''),
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

        if ($this->userId !== null) {
            $this->add('subscribe', 'checkbox', [
                'rules' => 'nullable|boolean',
                'label' => 'Obserwuj wątek',
                'value' => $this->isSubscribed()
            ]);
        }

        $this->add('attachments', 'collection', [
            'value' => $this->post->attachments()->get(),
            'template' => 'attachments',
            'child_attr' => [
                'type' => 'child_form',
                'class' => AttachmentForm::class,
            ]
        ]);

        $this->add('submit', 'submit', [
            'label' => 'Zapisz',
            'attr' => [
                'data-submit-state' => 'Zapisywanie...',
                'tabindex' => 4
            ]
        ]);
    }

    /**
     * Determine if user subscribes this topic or not...
     *
     * @return bool|mixed
     */
    protected function isSubscribed()
    {
        if ($this->userId === null) {
            return false;
        }

        if (!$this->topic->id) {
            $subscribe = $this->user->allow_subscribe;
        } else {
            $subscribe = $this->topic->subscribers()->forUser($this->userId)->exists();

            // we're creating new post...
            if ($this->post->id === null && $subscribe === false && $this->user->allow_subscribe) {
                $subscribe = false;

                // if this is the first post in this topic, subscribe option depends on user's default setting
                if (!$this->topic->users()->forUser($this->userId)->exists()) {
                    $subscribe = true;
                }
            }
        }

        return $subscribe;
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

    /**
     * @return bool
     */
    private function isUpdating()
    {
        return !empty($this->post->id);
    }

    /**
     * @return bool
     */
    private function canSeeUsername()
    {
        return $this->userId === null || (!empty($this->post->user_name) && $this->isUpdating());
    }
}
