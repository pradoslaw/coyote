<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Contracts\PostRepositoryInterface as Post;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Coyote\Parser\Reference\Login as Ref_Login;
use Coyote\Stream\Activities\Create as Stream_Create;
use Coyote\Stream\Objects\Topic as Stream_Topic;
use Illuminate\Http\Request;
use Coyote\Http\Requests\PostRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Cache;

class TopicController extends Controller
{
    use Base;

    /**
     * @var Forum
     */
    private $forum;

    /**
     * @var Topic
     */
    private $topic;

    /**
     * @var Post
     */
    private $post;

    /**
     * @param Forum $forum
     * @param Topic $topic
     * @param Post $post
     */
    public function __construct(Forum $forum, Topic $topic, Post $post)
    {
        parent::__construct();

        $this->forum = $forum;
        $this->topic = $topic;
        $this->post = $post;
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
        if ($topic->forum_id !== $forum->id) {
            return redirect(route('forum.topic', [$forum->path, $topic->id, $topic->path]));
        }

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
                        return response($url) . '?p=' . $postId . '#id' . $postId;
                    }
                }
            }
        }

        // current page...
        $page = $request->page;

        if ($request->has('p')) {
            $page = $this->post->getPage($request->get('p'), $topic->id);
        }

        // magic happens here. get posts for given topic
        $posts = $this->post->takeForTopic($topic->id, $topic->first_post_id, $userId, $page);
        $paginate = new LengthAwarePaginator($posts, $topic->replies, 10, $page, ['path' => ' ']);

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

            $markTime = $post->created_at;
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

        $this->breadcrumb($forum);
        $this->breadcrumb->push($topic->subject, route('forum.topic', [$forum->path, $topic->id, $topic->path]));

        // create forum list for current user (according to user's privileges)
        $this->pushForumCriteria();
        $forumList = $this->forum->forumList();

        $viewers = app('Session\Viewers')->render($request->getRequestUri());

        // let's cache tags. we don't need to run this query every time
        $tags = Cache::remember('forum:tags', 60 * 24, function () {
            return $this->forum->getTagClouds();
        });

        return parent::view('forum.topic')->with(compact('viewers', 'posts', 'forum', 'topic', 'paginate', 'forumList', 'tags'));
    }

    /**
     * @param Forum $forum
     * @return \Illuminate\View\View
     */
    public function submit($forum)
    {
        // make sure that user can write in this category
        $this->authorizeForum($forum);

        $this->breadcrumb($forum);
        $this->breadcrumb->push('Nowy wątek', route('forum.topic.submit', [$forum->path]));

        return parent::view('forum.submit', ['title' => 'Nowy wątek na ' . $forum->name])->with('forum', $forum);
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
            $this->post->create($request->all() + [
                'user_id'   => auth()->id(),
                'topic_id'  => $topic->id,
                'forum_id'  => $forum->id,
                'ip'        => request()->ip(),
                'browser'   => request()->browser(),
                'host'      => request()->server('SERVER_NAME')
            ]);

            $this->topic->setTags($topic->id, $request->get('tag'));

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

            stream(Stream_Create::class, (new Stream_Topic)->map($topic, $forum, $text));
            return route('forum.topic', [$forum->path, $topic->id, $path]);
        });

        return redirect()->to($url);
    }
}
