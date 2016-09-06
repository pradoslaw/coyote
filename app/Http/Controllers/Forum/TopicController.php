<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Forum;
use Coyote\Forum\Reason;
use Coyote\Http\Factories\CacheFactory;
use Coyote\Http\Factories\FlagFactory;
use Coyote\Http\Factories\StreamFactory;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\Repositories\Criteria\Post\ObtainSubscribers;
use Coyote\Repositories\Criteria\Post\WithTrashed;
use Coyote\Services\Elasticsearch\Factories\Forum\MoreLikeThisFactory;
use Coyote\Services\Parser\Parsers\ParserInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Coyote\Topic;

class TopicController extends BaseController
{
    use StreamFactory, CacheFactory, FlagFactory;

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

        // current page...
        $page = $request->get('page');
        // number of answers
        $replies = $topic->replies;
        // number of posts per one page
        $perPage = $this->postsPerPage($request);

        // user wants to show certain post. we need to calculate page number based on post id.
        if ($request->has('p')) {
            $page = $this->post->getPage($request->get('p'), $topic->id, $perPage);
        }

        $gate = $this->getGateFactory();

        // user with forum-update ability WILL see every post
        if ($gate->allows('delete', $forum)) {
            $this->post->pushCriteria(new WithTrashed());
            // user is able to see real number of posts in this topic
            $replies = $topic->replies_real;
        }

        start_measure('More like this');

        // build "more like this" block. it's important to send elasticsearch query before
        // send SQL query to database because search() method exists only in Model and not Builder class.
        $mlt = $this->getCacheFactory()->remember('mlt-post:' . $topic->id, 60 * 24, function () use ($topic) {
            $this->forum->pushCriteria(new OnlyThoseWithAccess());

            $builder = (new MoreLikeThisFactory())->build($topic, $this->forum->lists('id'));

            $build = $builder->build();
            debugbar()->debug($build);

            // search related topics
            $mlt = $this->topic->search($build);

            // it's important to reset criteria for the further queries
            $this->forum->resetCriteria();
            return $mlt;
        });

        stop_measure('More like this');

        $this->post->pushCriteria(new ObtainSubscribers($this->userId));

        // magic happens here. get posts for given topic
        /* @var \Illuminate\Support\Collection $posts */
        $posts = $this->post->takeForTopic($topic->id, $topic->first_post_id, $page, $perPage);
        $paginate = new LengthAwarePaginator($posts, $replies, $perPage, $page, ['path' => ' ']);

        start_measure('Parsing...');
        $parser = $this->getParsers();

        foreach ($posts as &$post) {
            // parse post or get it from cache
            $post->text = $parser['post']->parse($post->text);

            if ((auth()->guest() || (auth()->check() && auth()->user()->allow_sig)) && $post->sig) {
                $post->sig = $parser['sig']->parse($post->sig);
            }

            foreach ($post->comments as &$comment) {
                $comment->text = $parser['comment']->setUserId($comment->user_id)->parse($comment->text);
            }
        }

        stop_measure('Parsing...');

        $postsId = $posts->pluck('id')->toArray();
        $dateTimeString = $posts->last()->created_at->toDateTimeString();

        if ($markTime[Topic::class] < $dateTimeString && $markTime[Forum::class] < $dateTimeString) {
            // mark topic as read
            $topic->markAsRead($dateTimeString, $this->userId, $this->sessionId);
            $isUnread = true;

            if ($markTime[Forum::class] < $dateTimeString) {
                $isUnread = $this->topic->isUnread(
                    $forum->id,
                    $markTime[Forum::class],
                    $this->userId,
                    $this->sessionId
                );
            }

            if (!$isUnread) {
                $this->forum->markAsRead($forum->id, $this->userId, $this->sessionId);
            }
        }

        if ($gate->allows('delete', $forum) || $gate->allows('move', $forum)) {
            $reasonList = Reason::lists('name', 'id')->toArray();
        }

        $this->breadcrumb($forum);
        $this->breadcrumb->push($topic->subject, route('forum.topic', [$forum->slug, $topic->id, $topic->slug]));

        // create forum list for current user (according to user's privileges)
        $this->pushForumCriteria();
        $forumList = $this->forum->forumList();

        $form = $this->getForm($forum, $topic);

        return $this->view(
            'forum.topic',
            compact('posts', 'forum', 'topic', 'paginate', 'forumList', 'reasonList', 'form', 'mlt')
        )->with([
            'markTime'      => $markTime[Topic::class] ? $markTime[Topic::class] : $markTime[Forum::class],
            'flags'         => $this->getFlags($postsId),
            'warnings'      => $this->getWarnings($topic),
            'subscribers'   => auth()->check() ? $topic->subscribers()->lists('topic_id', 'user_id') : [],
            'activities'    => $this->getActivities($forum, $postsId)
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
        $gate = $this->getGateFactory();
        $flags = [];

        // @todo Jezeli raportowany jest post na forum to sprawdzane jest globalne uprawnienie danego
        // uzytkownika. Oznacza to, ze lokalni moderatorzy nie beda mogli czytac raportow
        if ($gate->allows('forum-delete')) {
            $flags = $this->getFlagFactory()->takeForPosts($postsId);
        }

        return $flags;
    }

    /**
     * @param \Coyote\Forum $forum
     * @param int[] $postsId
     * @return array
     */
    private function getActivities($forum, $postsId)
    {
        $gate = $this->getGateFactory();
        $activities = [];

        if ($gate->allows('delete', $forum)) {
            $activities = [];

            // here we go. if user has delete ability, for sure he/she would like to know
            // why posts were deleted and by whom
            $collection = $this->findByObject('Post', $postsId, 'Delete');

            foreach ($collection->sortByDesc('created_at')->groupBy('object.id') as $row) {
                $activities[$row->first()['object.id']] = $row->first();
            }
        }

        return $activities;
    }

    /**
     * @param \Coyote\Topic $topic
     * @return array
     */
    private function getWarnings($topic)
    {
        $warnings = [];

        // if topic is locked we need to fetch information when and by whom
        if ($topic->is_locked) {
            $warnings['lock'] = $this->findByObject('Topic', $topic->id, 'Lock')->last();
        }

        if ($topic->prev_forum_id) {
            $warnings['move'] = $this->findByObject('Topic', $topic->id, 'Move')->last();
        }

        return $warnings;
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
     * @param string $object
     * @param $id
     * @param string $verb
     * @return mixed
     */
    protected function findByObject($object, $id, $verb)
    {
        return $this->getStreamFactory()->findByObject($object, $id, $verb);
    }
}
