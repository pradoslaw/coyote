<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Events\TopicWasSaved;
use Coyote\Forum\Reason;
use Coyote\Http\Factories\CacheFactory;
use Coyote\Http\Factories\FlagFactory;
use Coyote\Http\Resources\PollResource;
use Coyote\Http\Resources\PostCollection;
use Coyote\Http\Resources\TopicResource;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\Repositories\Criteria\Post\WithSubscribers;
use Coyote\Repositories\Criteria\WithTrashed;
use Coyote\Repositories\Criteria\Post\WithTrashedInfo;
use Coyote\Services\Elasticsearch\Builders\Forum\MoreLikeThisBuilder;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\Forum\TreeBuilder\Builder;
use Coyote\Services\Forum\TreeBuilder\ListDecorator;
use Coyote\Topic;
use Illuminate\Http\Request;

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
        $this->breadcrumb->push($topic->subject, route('forum.topic', [$forum->slug, $topic->id, $topic->slug]));

        // get the topic (and forum) mark time value from middleware
        // @see \Coyote\Http\Middleware\ScrollToPost
        $markTime = $request->attributes->get('mark_time');

        $this->gate = $this->getGateFactory();

        // current page...
        $page = (int) $request->get('page');
        // number of posts per one page
        $perPage = $this->postsPerPage($request);

        // user with forum-update ability WILL see every post
        // NOTE: criteria MUST BE pushed before calling getPage() method!
        if ($this->gate->allows('delete', $forum)) {
            $this->post->pushCriteria(new WithTrashed());
            $this->post->pushCriteria(new WithTrashedInfo());

            // user is able to see real number of posts in this topic
            $topic->replies = $topic->replies_real;
        }

        // user wants to show certain post. we need to calculate page number based on post id.
        if ($request->filled('p')) {
            $page = $this->post->getPage(min(2147483647, (int) $request->get('p')), $topic->id, $perPage);
        }

        // show posts of last page if page parameter is higher than pages count
        $lastPage = max((int) ceil($topic->replies / $perPage), 1);
        if ($page > $lastPage) {
            $page = $lastPage;
        }

        $this->post->pushCriteria(new WithSubscribers($this->userId));

        // magic happens here. get posts for given topic (including first post for every page)
        /* @var \Illuminate\Support\Collection $posts */
        $paginate = $this->post->lengthAwarePagination($topic, $page, $perPage);

        // create forum list for current user (according to user's privileges)
        $treeBuilder = new Builder($this->forum->list());
        $treeDecorator = new ListDecorator($treeBuilder);

        $this->pushForumCriteria(true);
        $forumList = $treeDecorator->build();

        $tracker = Tracker::make($topic);

        $posts = (new PostCollection($paginate))
            ->setRelations($topic, $forum)
            ->setTracker($tracker);

        $allForums = $reasons = [];

        if ($this->gate->allows('delete', $forum) || $this->gate->allows('move', $forum)) {
            $postIds = $paginate->pluck('id')->toArray();
            $reasons = Reason::pluck('name', 'id')->toArray();

            if ($this->gate->allows('delete', $forum)) {
                $posts->setFlags($this->getFlags($postIds));
            }

            $this->forum->skipCriteria(true);

            $treeBuilder->setForums($this->forum->list());
            $allForums = $treeDecorator->setKey('id')->build();
        }

        $dateTime = $paginate->last()->created_at;
        // first, build array of posts with info which posts have been read
        $posts = $posts->resolve($this->request);

        // ..then, mark topic as read
        if ($markTime < $dateTime) {
            $tracker->asRead($dateTime);
        }

        $topic->load('tags');

        return $this->view('forum.topic', compact('posts', 'forum', 'paginate', 'forumList', 'reasons'))->with([
            'mlt'           => $this->moreLikeThis($topic),
            'model'         => $topic, // we need eloquent model in twig to show information about locked/moved topic
            'topic'         => (new TopicResource($tracker))->resolve($request),
            'poll'          => $topic->poll ? (new PollResource($topic->poll))->resolve($request) : null,
            'is_writeable'  => $this->gate->allows('write', $forum) && $this->gate->allows('write', $topic),
            'all_forums'    => $allForums,
            'description'   => excerpt(array_first($posts)['text'], 100)
        ]);
    }

    private function moreLikeThis(Topic $topic)
    {
        // build "more like this" block. it's important to send elasticsearch query before
        // send SQL query to database because search() method exists only in Model and not Builder class.
        return $this->getCacheFactory()->remember('mlt-post:' . $topic->id, now()->addDay(), function () use ($topic) {
            // it's important to reset criteria for the further queries
            $this->forum->resetCriteria();

            $this->forum->pushCriteria(new OnlyThoseWithAccess());

            $builder = new MoreLikeThisBuilder($topic, $this->forum->pluck('id'));

            // search related topics
            $mlt = $this->topic->search($builder);

            return $mlt;
        });
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

        event(new TopicWasSaved($topic));

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
        $posts->load('comments:id,post_id,user_id'); // load comments assigned to posts

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

        $tracker->asRead($topic->last_post_created_at);
    }
}
