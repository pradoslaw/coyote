<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Forum\Reason;
use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Contracts\PostRepositoryInterface as Post;
use Coyote\Repositories\Contracts\StreamRepositoryInterface as Stream;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Coyote\Parser\Reference\Login as Ref_Login;
use Coyote\Repositories\Criteria\Post\WithTrashed;
use Coyote\Stream\Activities\Create as Stream_Create;
use Coyote\Stream\Objects\Topic as Stream_Topic;
use Coyote\Stream\Objects\Forum as Stream_Forum;
use Coyote\Stream\Actor as Stream_Actor;
use Illuminate\Http\Request;
use Coyote\Topic\Subscriber as Topic_Subscriber;
use Coyote\Post\Subscriber as Post_Subscriber;
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
     * @param Forum $forum
     * @param Topic $topic
     * @param Post $post
     * @param Stream $stream
     */
    public function __construct(Forum $forum, Topic $topic, Post $post, Stream $stream)
    {
        parent::__construct($forum, $topic);

        $this->post = $post;
        $this->stream = $stream;
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

        if ($request->has('p')) {
            $page = $this->post->getPage($request->get('p'), $topic->id);
        }

        // user with forum-update ability WILL see every post
        if (Gate::allows('delete', $forum)) {
            $this->post->pushCriteria(new WithTrashed());
            $replies = $topic->replies_real;
        }

        // magic happens here. get posts for given topic
        $posts = $this->post->takeForTopic($topic->id, $topic->first_post_id, $userId, $page);
        $paginate = new LengthAwarePaginator($posts, $replies, 10, $page, ['path' => ' ']);

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

            // here we go. if user has delete ability, for sure he/she would like to know
            // why posts were deleted and by whom
            $collection = $this->stream->findByObject('Post', $posts->pluck('id'), 'Delete');

            foreach ($collection->sortByDesc('created_at')->groupBy('object.id') as $row) {
                $activities[$row->first()['object.id']] = $row->first();
            }

            $reasonList = Reason::lists('name', 'id');
        }

        $this->breadcrumb($forum);
        $this->breadcrumb->push($topic->subject, route('forum.topic', [$forum->path, $topic->id, $topic->path]));

        // create forum list for current user (according to user's privileges)
        $this->pushForumCriteria();
        $forumList = $this->forum->forumList();

        return $this->view('forum.topic')->with(
            compact('posts', 'forum', 'topic', 'paginate', 'forumList', 'activities', 'reasonList')
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

        return Controller::view('forum.submit', ['title' => 'Nowy wątek na ' . $forum->name])->with('forum', $forum);
    }

    /**
     * @param $forum
     * @param PostRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save($forum, PostRequest $request)
    {
        $url = \DB::transaction(function () use ($request, $forum) {
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

            $this->topic->setTags($topic->id, $request->get('tag', []));

            if (auth()->check()) {
                $this->topic->subscribe($topic->id, auth()->id(), $request->get('subscribe'));
                // automatically subscribe post
                $this->post->subscribe($post->id, auth()->id(), true);
            }

            // parsing text and store it in cache
            $text = app()->make('Parser\Post')->parse($request->text);

            // get id of users that were mentioned in the text
            $usersId = (new Ref_Login())->grab($text);

            if ($usersId) {
                app()->make('Alert\Post\Login')->with([
                    'users_id'    => $usersId,
                    'sender_id'   => auth()->id(),
                    'sender_name' => $request->get('user_name', auth()->user()->name),
                    'subject'     => excerpt($request->subject, 48),
                    'excerpt'     => excerpt($text),
                    'url'         => route('forum.topic', [$forum->path, $topic->id, $path], false)
                ])->notify();
            }

            $actor = new Stream_Actor(auth()->user());

            if (auth()->guest()) {
                $actor->displayName = $request->get('user_name');
            }
            (new \Coyote\Stream\Stream($this->stream))->add(
                new Stream_Create(
                    $actor,
                    (new Stream_Topic)->map($topic, $forum, $text),
                    (new Stream_Forum)->map($forum)
                )
            );

            return route('forum.topic', [$forum->path, $topic->id, $path]);
        });

        return redirect()->to($url);
    }

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
}
