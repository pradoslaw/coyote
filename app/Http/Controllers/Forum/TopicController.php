<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Forum\Reason;
use Coyote\Http\Factories\GateFactory;
use Coyote\Http\Factories\StreamFactory;
use Coyote\Repositories\Contracts\FlagRepositoryInterface;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Repositories\Criteria\Post\WithTrashed;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TopicController extends BaseController
{
    use StreamFactory, GateFactory;

    /**
     * @param \Coyote\Forum $forum
     * @param \Coyote\Topic $topic
     * @param string $slug
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function index($forum, $topic, $slug, Request $request)
    {
        // pobranie daty i godziny ostatniego razu gdy uzytkownik przeczytal ten watek
        $topicMarkTime = $topic->markTime($this->userId, $this->sessionId);
        // pobranie daty i godziny ostatniego razy gdy uzytkownik przeczytal to forum
        $forumMarkTime = $forum->markTime($this->userId, $this->sessionId);

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

        $gate = $this->getGateFactory();

        // user with forum-update ability WILL see every post
        if ($gate->allows('delete', $forum)) {
            $this->post->pushCriteria(new WithTrashed());
            $replies = $topic->replies_real;
        }

        // magic happens here. get posts for given topic
        $posts = $this->post->takeForTopic($topic->id, $topic->first_post_id, $this->userId, $page, $perPage);
        $paginate = new LengthAwarePaginator($posts, $replies, $perPage, $page, ['path' => ' ']);

        $parser = [
            'post' => app()->make('Parser\Post'),
            'comment' => app()->make('Parser\Comment'),
            'sig' => app()->make('Parser\Sig')
        ];

        $markTime = null;
        start_measure('Parsing...');

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

        stop_measure('Parsing...');

        if ($topicMarkTime < $markTime && $forumMarkTime < $markTime) {
            // mark topic as read
            $topic->markAsRead($markTime, $this->userId, $this->sessionId);
            $isUnread = true;

            if ($forumMarkTime < $markTime) {
                $isUnread = $this->topic->isUnread($forum->id, $forumMarkTime, $this->userId, $this->sessionId);
            }

            if (!$isUnread) {
                $this->forum->markAsRead($forum->id, $this->userId, $this->sessionId);
            }
        }

        if ($gate->allows('delete', $forum)) {
            $activities = [];
            $postsId = $posts->pluck('id')->toArray();

            // here we go. if user has delete ability, for sure he/she would like to know
            // why posts were deleted and by whom
            $collection = $this->findByObject('Post', $postsId, 'Delete');

            foreach ($collection->sortByDesc('created_at')->groupBy('object.id') as $row) {
                $activities[$row->first()['object.id']] = $row->first();
            }

            // @todo Jezeli raportowany jest post na forum to sprawdzane jest globalne uprawnienie danego
            // uzytkownika. Oznacza to, ze lokalni moderatorzy nie beda mogli czytac raportow
            if ($gate->allows('forum-delete')) {
                $flags = app(FlagRepositoryInterface::class)->takeForPosts($postsId);
            }
        }

        if ($gate->allows('delete', $forum) || $gate->allows('move', $forum)) {
            $reasonList = Reason::lists('name', 'id')->toArray();
        }

        $warnings = [];

        // if topic is locked we need to fetch information when and by whom
        if ($topic->is_locked) {
            $warnings['lock'] = $this->findByObject('Topic', $topic->id, 'Lock')->last();
        }

        if ($topic->prev_forum_id) {
            $warnings['move'] = $this->findByObject('Topic', $topic->id, 'Move')->last();
        }

        // increase topic views counter
        // only for developing purposes. it increases counter every time user hits the page
        if (\App::environment('local', 'dev')) {
            $this->topic->addViews($topic->id);
        } else {
            // on production environment: store hit in redis
            app('redis')->sadd(
                'counter:topic:' . $topic->id,
                $this->userId ?: $this->sessionId . ';' . round(time() / 300) * 300
            );
        }

        if (auth()->check()) {
            $subscribers = $topic->subscribers()->lists('topic_id', 'user_id');
            $subscribe = isset($subscribers[$this->userId]);

            if (!$subscribe && auth()->user()->allow_subscribe) {
                // if this is the first post in this topic, subscribe option depends on user's default setting
                if (!$topic->users()->forUser($this->userId)->exists()) {
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
            compact('posts', 'forum', 'topic', 'paginate', 'forumList', 'activities', 'reasonList', 'warnings', 'subscribers', 'subscribe', 'flags')
        );
    }

    /**
     * @param \Coyote\Topic $topic
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function subscribe($topic)
    {
        $subscriber = $topic->subscribers()->forUser($this->userId)->first();

        if ($subscriber) {
            $subscriber->delete();
        } else {
            $topic->subscribers()->create(['user_id' => $this->userId]);
        }

        return response($topic->subscribers()->count());
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
     * @param \Coyote\Topic $topic
     */
    public function mark($topic)
    {
        // pobranie daty i godziny ostatniego razy gdy uzytkownik przeczytal to forum
        $forumMarkTime = $topic->forum->markTime($this->userId, $this->sessionId);

        // mark topic as read
        $topic->markAsRead($topic->last_post_created_at, $this->userId, $this->sessionId);
        $isUnread = $this->topic->isUnread($topic->forum_id, $forumMarkTime, $this->userId, $this->sessionId);

        if (!$isUnread) {
            $this->forum->markAsRead($topic->forum_id, $this->userId, $this->sessionId);
        }
    }

    /**
     * @param $object
     * @param $id
     * @param $verb
     * @return mixed
     */
    protected function findByObject($object, $id, $verb)
    {
        return $this->getStreamFactory()->findByObject($object, $id, $verb);
    }
}
