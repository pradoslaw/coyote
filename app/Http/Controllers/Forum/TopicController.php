<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Forum\Reason;
use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Contracts\Post\AttachmentRepositoryInterface as Attachment;
use Coyote\Repositories\Contracts\Post\LogRepositoryInterface;
use Coyote\Repositories\Contracts\PostRepositoryInterface as Post;
use Coyote\Repositories\Contracts\StreamRepositoryInterface as Stream;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Coyote\Parser\Reference\Login as Ref_Login;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Repositories\Criteria\Post\WithTrashed;
use Coyote\Stream\Activities\Create as Stream_Create;
use Coyote\Stream\Activities\Update as Stream_Update;
use Coyote\Stream\Activities\Lock as Stream_Lock;
use Coyote\Stream\Activities\Unlock as Stream_Unlock;
use Coyote\Stream\Activities\Move as Stream_Move;
use Coyote\Stream\Objects\Topic as Stream_Topic;
use Coyote\Stream\Objects\Post as Stream_Post;
use Coyote\Stream\Objects\Forum as Stream_Forum;
use Coyote\Stream\Actor as Stream_Actor;
use Illuminate\Http\Request;
use Coyote\Topic\Subscriber as Topic_Subscriber;
use Coyote\Http\Requests\PostRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Gate;

class TopicController extends BaseController
{
    /**
     * @var Post
     */
    private $post;

    /**
     * @var Stream
     */
    private $stream;

    /**
     * @var Attachment
     */
    private $attachment;

    /**
     * @param Forum $forum
     * @param Topic $topic
     * @param Post $post
     * @param Stream $stream
     * @param Attachment $attachment
     */
    public function __construct(Forum $forum, Topic $topic, Post $post, Stream $stream, Attachment $attachment)
    {
        parent::__construct($forum, $topic);

        $this->post = $post;
        $this->stream = $stream;
        $this->attachment = $attachment;
    }

    /**
     * @param \Coyote\Forum $forum
     * @param \Coyote\Topic $topic
     * @param string $slug
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function index($forum, $topic, $slug, Request $request)
    {
        $userId = auth()->id();
        $sessionId = $request->session()->getId();

        // pobranie daty i godziny ostatniego razu gdy uzytkownik przeczytal ten watek
        $topicMarkTime = $this->topic->markTime($topic->id, $userId, $sessionId);
        // pobranie daty i godziny ostatniego razy gdy uzytkownik przeczytal to forum
        $forumMarkTime = $this->forum->markTime($forum->id, $userId, $sessionId);

        if ($request->get('view') === 'unread') {
            if ($topicMarkTime < $topic->last_post_created_at && $forumMarkTime < $topic->last_post_created_at) {
                $markTime = max($topicMarkTime, $forumMarkTime);

                if ($markTime) {
                    $postId = $this->post->getFirstUnreadPostId($topic->id, $markTime);

                    if ($postId && $postId !== $topic->first_post_id) {
                        $url = route('forum.topic', [$forum->path, $topic->id, $topic->path]);
                        return redirect()->to($url . '?p=' . $postId . '#id' . $postId);
                    }
                }
            }
        }

        // current page...
        $page = $request->page;
        // number of answers
        $replies = $topic->replies;

        if ($request->has('perPage')) {
            $perPage = max(10, min($request->get('perPage'), 50));
            $this->setSetting('forum.posts_per_page', $perPage);
        } else {
            $perPage = $this->getSetting('forum.posts_per_page', 10);
        }

        // user wants to show certain post. we need to calculate page number based on post id.
        if ($request->has('p')) {
            $page = $this->post->getPage($request->get('p'), $topic->id);
        }

        // user with forum-update ability WILL see every post
        if (Gate::allows('delete', $forum)) {
            $this->post->pushCriteria(new WithTrashed());
            $replies = $topic->replies_real;
        }

        // magic happens here. get posts for given topic
        $posts = $this->post->takeForTopic($topic->id, $topic->first_post_id, $userId, $page, $perPage);
        $paginate = new LengthAwarePaginator($posts, $replies, $perPage, $page, ['path' => ' ']);

        $parser = [
            'post' => app()->make('Parser\Post'),
            'comment' => app()->make('Parser\Comment'),
            'sig' => app()->make('Parser\Sig')
        ];

        $markTime = null;

        foreach ($posts as &$post) {
            // parse post or get it from cache
            $post->text = $parser['post']->parse($post->text);

            if ((auth()->guest() || (auth()->check() && auth()->user()->allow_sig)) && $post->sig) {
                $post->sig = $parser['sig']->parse($post->sig);
            }

            foreach ($post->comments as &$comment) {
                $comment->text = $parser['comment']->parse($comment->text);
            }

            $markTime = $post->created_at->toDateTimeString();
        }

        if ($topicMarkTime < $markTime && $forumMarkTime < $markTime) {
            // mark topic as read
            $this->topic->markAsRead($topic->id, $forum->id, $markTime, $userId, $sessionId);
            $isUnread = true;

            if ($forumMarkTime < $markTime) {
                $isUnread = $this->topic->isUnread($forum->id, $forumMarkTime, $userId, $sessionId);
            }

            if (!$isUnread) {
                $this->forum->markAsRead($forum->id, $userId, $sessionId);
            }
        }

        if (Gate::allows('delete', $forum)) {
            $activities = [];
            $postsId = $posts->pluck('id')->toArray();

            // here we go. if user has delete ability, for sure he/she would like to know
            // why posts were deleted and by whom
            $collection = $this->stream->findByObject('Post', $postsId, 'Delete');

            foreach ($collection->sortByDesc('created_at')->groupBy('object.id') as $row) {
                $activities[$row->first()['object.id']] = $row->first();
            }

            $flags = app()->make('FlagRepository')->takeForPosts($postsId);
        }

        if (Gate::allows('delete', $forum) || Gate::allows('move', $forum)) {
            $reasonList = Reason::lists('name', 'id')->toArray();
        }

        // if topic is locked we need to fetch information when and by whom
        if ($topic->is_locked) {
            $lock = $this->stream->findByObject('Topic', $topic->id, 'Lock')->last();
        }

        // increase topic views counter
        // only for developing purposes. it increases counter every time user hits the page
        if (\App::environment('local', 'dev')) {
            $this->topic->addViews($topic->id);
        } else {
            $user = auth()->check() ? auth()->id() : $request->session()->getId();
            // on production environment: store hit in redis
            app('redis')->sadd('counter:topic:' . $topic->id, $user . ';' . round(time() / 300) * 300);
        }

        if (auth()->check()) {
            $subscribers = $topic->subscribers()->lists('topic_id', 'user_id');
            $subscribe = isset($subscribers[auth()->id()]);

            if (!$subscribe && auth()->user()->allow_subscribe) {
                // if this is the first post in this topic, subscribe option depends on user's default setting
                if (!$topic->users()->where('user_id', auth()->id())->count()) {
                    $subscribe = true;
                }
            }
        }

        $this->breadcrumb($forum);
        $this->breadcrumb->push($topic->subject, route('forum.topic', [$forum->path, $topic->id, $topic->path]));

        // create forum list for current user (according to user's privileges)
        $this->pushForumCriteria();
        $forumList = $this->forum->forumList();

        return $this->view('forum.topic', ['markTime' => $topicMarkTime ? $topicMarkTime : $forumMarkTime])->with(
            compact('posts', 'forum', 'topic', 'paginate', 'forumList', 'activities', 'reasonList', 'lock', 'subscribers', 'subscribe', 'flags')
        );
    }

    /**
     * @param Forum $forum
     * @return \Illuminate\View\View
     */
    public function submit($forum)
    {
        $this->breadcrumb($forum);
        $this->breadcrumb->push('Nowy wątek', route('forum.topic.submit', [$forum->path]));

        if (request()->old('attachments')) {
            $attachments = $this->attachment->findByFile(request()->old('attachments'));
        }

        if (auth()->check()) {
            // default subscribe setting
            $subscribe = auth()->user()->allow_subscribe;
        }

        return Controller::view('forum.submit')->with(compact('forum', 'attachments', 'subscribe'));
    }

    /**
     * @param $forum
     * @param PostRequest $request
     * @param LogRepositoryInterface $log
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save($forum, PostRequest $request, LogRepositoryInterface $log)
    {
        $url = \DB::transaction(function () use ($request, $forum, $log) {
            $path = str_slug($request->get('subject'), '_');

            // create new topic
            $topic = $this->topic->create($request->all() + ['path' => $path, 'forum_id' => $forum->id]);
            // create new post and assign it to topic. don't worry about the rest: trigger will do the work
            $post = $this->post->create($request->all() + [
                'user_id'   => auth()->id(),
                'topic_id'  => $topic->id,
                'forum_id'  => $forum->id,
                'ip'        => request()->ip(),
                'browser'   => request()->browser(),
                'host'      => request()->server('SERVER_NAME')
            ]);

            // assign attachments to the post
            $this->post->setAttachments($post->id, $request->get('attachments', []));
            // assign tags to topic
            $this->topic->setTags($topic->id, $request->get('tag', []));

            // save it in log...
            $log->add($post->id, auth()->id(), $post->text, $topic->subject, $request->get('tag', []));

            if (auth()->check()) {
                $this->topic->subscribe($topic->id, auth()->id(), $request->get('subscribe'));
                // automatically subscribe post
                $this->post->subscribe($post->id, auth()->id(), true);
            }

            // parsing text and store it in cache
            // it's important. don't remove below line so that text in activity can be saved without markdown
            $post->text = app()->make('Parser\Post')->parse($request->text);

            // get id of users that were mentioned in the text
            $usersId = (new Ref_Login())->grab($post->text);

            if ($usersId) {
                app()->make('Alert\Post\Login')->with([
                    'users_id'    => $usersId,
                    'sender_id'   => auth()->id(),
                    'sender_name' => $request->get('user_name', auth()->user()->name),
                    'subject'     => excerpt($request->subject),
                    'excerpt'     => excerpt($post->text),
                    'url'         => route('forum.topic', [$forum->path, $topic->id, $path], false)
                ])->notify();
            }

            $actor = new Stream_Actor(auth()->user());

            if (auth()->guest()) {
                $actor->displayName = $request->get('user_name');
            }
            app()->make('stream')->add(
                new Stream_Create(
                    $actor,
                    (new Stream_Topic)->map($topic, $forum, $post->text),
                    (new Stream_Forum)->map($forum)
                )
            );

            return route('forum.topic', [$forum->path, $topic->id, $path]);
        });

        return redirect()->to($url);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function subscribe($id)
    {
        $subscriber = Topic_Subscriber::where('topic_id', $id)->where('user_id', auth()->id())->first();

        if ($subscriber) {
            $subscriber->delete();
        } else {
            Topic_Subscriber::create(['topic_id' => $id, 'user_id' => auth()->id()]);
        }

        return response(Topic_Subscriber::where('topic_id', $id)->count());
    }

    /**
     * @param $id
     * @param User $user
     * @param Request $request
     * @return $this
     */
    public function prompt($id, User $user, Request $request)
    {
        $this->validate($request, ['q' => 'username']);
        $usersId = [];

        $posts = $this->post->findAllBy('topic_id', $id, ['id', 'user_id']);
        $posts->load('comments'); // load comments assigned to posts

        foreach ($posts as $post) {
            if ($post->user_id) {
                $usersId[] = $post->user_id;
            }

            foreach ($post->comments as $comment) {
                if ($comment->user_id) {
                    $usersId[] = $comment->user_id;
                }
            }
        }

        return view('components.prompt')->with('users', $user->lookupName($request['q'], array_unique($usersId)));
    }

    /**
     * @param int $id
     */
    public function lock($id)
    {
        $topic = $this->topic->findOrFail($id);
        $forum = $this->forum->find($topic->forum_id);

        $this->authorize('lock', $forum);

        \DB::transaction(function () use ($id, $topic, $forum) {
            $this->topic->lock($id, !$topic->is_locked);

            stream(
                $topic->is_locked ? Stream_Unlock::class : Stream_Lock::class,
                (new Stream_Topic())->map($topic, $forum)
            );
        });
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function move($id, Request $request)
    {
        $rules = ['path' => 'required|exists:forums'];

        // it must be like that. only if reason has been chosen, we need to validate it.
        if ($request->get('reason')) {
            $rules['reason'] = 'int|exists:forum_reasons,id';
        }
        $this->validate($request, $rules);

        $topic = $this->topic->findOrFail($id);
        $old = $this->forum->find($topic->forum_id);

        $this->authorize('move', $old);
        $forum = $this->forum->findBy('path', $request->get('path'));

        if (!$forum->userCanAccess(auth()->id())) {
            abort(401);
        }

        \DB::transaction(function () use ($topic, $forum, $old, $request) {
            $reason = null;

            $notification = [
                'sender_id'   => auth()->id(),
                'sender_name' => auth()->user()->name,
                'subject'     => excerpt($topic->subject, 48),
                'forum'       => $forum->name
            ];

            if ($request->get('reason')) {
                $reason = Reason::find($request->get('reason'));

                $notification = array_merge($notification, [
                    'excerpt'       => $reason->name,
                    'reasonName'    => $reason->name,
                    'reasonText'    => $reason->description
                ]);
            }

            $topic->forum_id = $forum->id;
            $topic->save();

            $object = (new Stream_Topic())->map($topic, $old);

            if (!empty($reason)) {
                $object->reasonName = $reason->name;
            }

            $post = $this->post->find($topic->first_post_id, ['user_id']);

            if ($post->user_id) {
                app()->make('Alert\Topic\Move')
                    ->with($notification)
                    ->setUrl($object->url)
                    ->setUserId($post->user_id)
                    ->notify();
            }

            stream(Stream_Move::class, $object, (new Stream_Forum())->map($forum));
        });

        return redirect()->route('forum.topic', [$forum->path, $topic->id, $topic->path])->with('success', 'Wątek został przeniesiony');
    }

    /**
     * @param Topic $topic
     */
    public function mark($topic)
    {
        $sessionId = request()->session()->getId();
        $userId = auth()->id();

        // pobranie daty i godziny ostatniego razy gdy uzytkownik przeczytal to forum
        $forumMarkTime = $this->forum->markTime($topic->forum_id, $userId, $sessionId);

        // mark topic as read
        $this->topic->markAsRead($topic->id, $topic->forum_id, $topic->last_post_created_at, $userId, $sessionId);
        $isUnread = $this->topic->isUnread($topic->forum_id, $forumMarkTime, $userId, $sessionId);

        if (!$isUnread) {
            $this->forum->markAsRead($topic->forum_id, $userId, $sessionId);
        }
    }

    /**
     * @param $topic
     * @param Request $request
     * @param LogRepositoryInterface $log
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function subject($topic, Request $request, LogRepositoryInterface $log)
    {
        $forum = $this->forum->find($topic->forum_id);
        $this->authorize('update', $forum);

        $this->validate($request, ['subject' => 'required|min:3|max:200']);

        $url = \DB::transaction(function () use ($request, $forum, $topic, $log) {
            $path = str_slug($request->get('subject'), '_');
            $topic->fill(['subject' => $request->get('subject'), 'path' => $path]);

            $post = $this->post->find($topic->first_post_id);

            if ($topic->isDirty()) {
                $topic->save();
                $tags = $topic->tags->lists('name')->toArray();

                // save it in log...
                $log->add($post->id, auth()->id(), $post->text, $topic->subject, $tags);
            }

            $url = route('forum.topic', [$forum->path, $topic->id, $topic->path], false);

            // put action into activity stream
            stream(
                Stream_Update::class,
                (new Stream_Post(['url' => $url]))->map($post),
                (new Stream_Topic())->map($topic, $forum)
            );

            return $url;
        });

        if ($request->ajax()) {
            return response(url($url));
        } else {
            return redirect()->to($url);
        }
    }
}
