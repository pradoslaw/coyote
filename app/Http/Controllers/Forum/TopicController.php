<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Forum\Reason;
use Coyote\Http\Factories\CacheFactory;
use Coyote\Http\Factories\FlagFactory;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\Repositories\Criteria\Post\WithSubscribers;
use Coyote\Repositories\Criteria\WithTrashed;
use Coyote\Repositories\Criteria\Post\WithTrashedInfo;
use Coyote\Services\Elasticsearch\Builders\Forum\MoreLikeThisBuilder;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\Forum\TreeBuilder;
use Coyote\Services\Parser\Parsers\ParserInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TopicController extends BaseController
{
    use CacheFactory, FlagFactory;

    /**
     * @var \Illuminate\Contracts\Auth\Access\Gate
     */
    private $gate;

    /**
     * @param Request $request
     * @param \Coyote\Forum $forum
     * @param \Coyote\Topic $topic
     * @return \Illuminate\View\View
     */
    public function index(Request $request, $forum, $topic)
    {
        // get the topic (and forum) mark time value from middleware
        // @see \Coyote\Http\Middleware\ScrollToPost
        $markTime = $request->attributes->get('mark_time');

        $this->gate = $this->getGateFactory();

        // current page...
        $page = (int) $request->get('page');
        // number of answers
        $replies = $topic->replies;
        // number of posts per one page
        $perPage = $this->postsPerPage($request);

        // user with forum-update ability WILL see every post
        // NOTE: criteria MUST BE pushed before calling getPage() method!
        if ($this->gate->allows('delete', $forum)) {
            $this->post->pushCriteria(new WithTrashed());
            $this->post->pushCriteria(new WithTrashedInfo());

            // user is able to see real number of posts in this topic
            $replies = $topic->replies_real;
        }

        // user wants to show certain post. we need to calculate page number based on post id.
        if ($request->filled('p')) {
            $page = $this->post->getPage(min(2147483647, (int) $request->get('p')), $topic->id, $perPage);
        }

        // show posts of last page if page parameter is higher than pages count
        $lastPage = max((int) ceil($replies / $perPage), 1);
        if ($page > $lastPage) {
            $page = $lastPage;
        }

        // build "more like this" block. it's important to send elasticsearch query before
        // send SQL query to database because search() method exists only in Model and not Builder class.
        $mlt = $this->getCacheFactory()->remember('mlt-post:' . $topic->id, 60 * 24, function () use ($topic) {
            $this->forum->pushCriteria(new OnlyThoseWithAccess());

            $builder = new MoreLikeThisBuilder($topic, $this->forum->pluck('id'));

            // search related topics
            $mlt = $this->topic->search($builder);

            // it's important to reset criteria for the further queries
            $this->forum->resetCriteria();
            return $mlt;
        });

        $this->post->pushCriteria(new WithSubscribers($this->userId));

        // magic happens here. get posts for given topic (including first post for every page)
        /* @var \Illuminate\Support\Collection $posts */
        $posts = $this->post->takeForTopic($topic->id, $topic->first_post_id, $page, $perPage);
        $paginate = new LengthAwarePaginator($posts, $replies, $perPage, $page, ['path' => ' ']);

        start_measure('Parsing...');
        $parser = $this->getParsers();

        /** @var \Coyote\Post $post */
        foreach ($posts as &$post) {
            // parse post or get it from cache
            $post->text = $parser['post']->parse($post->text);

            if ((auth()->guest() || (auth()->check() && $this->auth->allow_sig)) && $post->sig) {
                $post->sig = $parser['sig']->parse($post->sig);
            }

            foreach ($post->comments as &$comment) {
                $comment->text = $parser['comment']->setUserId($comment->user_id)->parse($comment->text);
            }

            $post->setRelation('topic', $topic);
            $post->setRelation('forum', $forum);
        }

        stop_measure('Parsing...');

        $postIds = $posts->pluck('id')->toArray();
        $dateTime = $posts->last()->created_at;

        if ($markTime < $dateTime) {
            Tracker::make($topic)->asRead($this->guestId, $dateTime);
        }

        // create forum list for current user (according to user's privileges)
        $this->pushForumCriteria();

        $treeBuilder = new TreeBuilder();
        $forumList = $treeBuilder->listBySlug($this->forum->list());

        $this->breadcrumb->push($topic->subject, route('forum.topic', [$forum->slug, $topic->id, $topic->slug]));

        $flags = $activities = $adminForumList = $reasonList = [];

        if ($this->gate->allows('delete', $forum) || $this->gate->allows('move', $forum)) {
            $reasonList = Reason::pluck('name', 'id')->toArray();

            if ($this->gate->allows('delete', $forum)) {
                $flags = $this->getFlags($postIds);
            }

            $this->forum->skipCriteria(true);
            $adminForumList = $treeBuilder->listBySlug($this->forum->list());
        }

        $form = $this->getForm($forum, $topic);

        return $this->view(
            'forum.topic',
            compact('posts', 'forum', 'topic', 'paginate', 'forumList', 'adminForumList', 'reasonList', 'form', 'mlt', 'flags')
        )->with([
            'markTime'      => $markTime,
            'subscribers'   => $this->userId ? $topic->subscribers()->pluck('topic_id', 'user_id') : [],
            'author_id'     => $posts[0]->user_id
        ]);
    }

    /**
     * @return ParserInterface[]
     */
    private function getParsers()
    {
        return [
            'post'      => app('parser.post'),
            'comment'   => app('parser.comment'),
            'sig'       => app('parser.sig')
        ];
    }

    /**
     * @param array $postsId
     * @return mixed
     */
    private function getFlags($postsId)
    {
        return $this->getFlagFactory()->takeForPosts($postsId);
    }

    /**
     * @param \Coyote\Topic $topic
     * @return \Symfony\Component\HttpFoundation\Response
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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function prompt($id, User $user, Request $request)
    {
        $this->validate($request, ['q' => 'username']);

        $posts = $this->post->findAllBy('topic_id', $id, ['id', 'user_id']);
        $posts->load('comments'); // load comments assigned to posts

        $usersId = $posts->pluck('user_id')->toArray();

        $posts->pluck('comments')[0]->each(function ($comment) use (&$usersId) {
            $usersId[] = $comment->user_id;
        });

        return view('components.prompt')->with('users', $user->lookupName($request['q'], array_filter(array_unique($usersId))));
    }

    /**
     * @param \Coyote\Topic $topic
     */
    public function mark($topic)
    {
        $tracker = Tracker::make($topic);

        $tracker->asRead($this->guestId, $topic->last_post_created_at);
    }
}
