<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Events\TopicWasSaved;
use Coyote\Events\PostWasSaved;
use Coyote\Forum\Reason;
use Coyote\Http\Requests\SubjectRequest;
use Coyote\Post\Log;
use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\FlagRepositoryInterface;
use Coyote\Repositories\Contracts\Post\AttachmentRepositoryInterface;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Coyote\Parser\Reference\Login as Ref_Login;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Repositories\Criteria\Post\WithTrashed;
use Coyote\Stream\Activities\Create as Stream_Create;
use Coyote\Stream\Activities\Update as Stream_Update;
use Coyote\Stream\Objects\Topic as Stream_Topic;
use Coyote\Stream\Objects\Post as Stream_Post;
use Coyote\Stream\Objects\Forum as Stream_Forum;
use Coyote\Stream\Actor as Stream_Actor;
use Illuminate\Http\Request;
use Coyote\Http\Requests\PostRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Gate;

class TopicController extends BaseController
{
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

        // user with forum-update ability WILL see every post
        if (Gate::allows('delete', $forum)) {
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

        if (Gate::allows('delete', $forum)) {
            $activities = [];
            $postsId = $posts->pluck('id')->toArray();

            // here we go. if user has delete ability, for sure he/she would like to know
            // why posts were deleted and by whom
            $collection = app()->make('Stream')->findByObject('Post', $postsId, 'Delete');

            foreach ($collection->sortByDesc('created_at')->groupBy('object.id') as $row) {
                $activities[$row->first()['object.id']] = $row->first();
            }

            $flags = app(FlagRepositoryInterface::class)->takeForPosts($postsId);
        }

        if (Gate::allows('delete', $forum) || Gate::allows('move', $forum)) {
            $reasonList = Reason::lists('name', 'id')->toArray();
        }

        // if topic is locked we need to fetch information when and by whom
        if ($topic->is_locked) {
            $lock = app()->make('Stream')->findByObject('Topic', $topic->id, 'Lock')->last();
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
            compact('posts', 'forum', 'topic', 'paginate', 'forumList', 'activities', 'reasonList', 'lock', 'subscribers', 'subscribe', 'flags')
        );
    }

    /**
     * @param Request $request
     * @param \Coyote\Forum $forum
     * @return \Illuminate\View\View
     */
    public function submit(Request $request, $forum)
    {
        $this->breadcrumb($forum);
        $this->breadcrumb->push('Nowy wątek', route('forum.topic.submit', [$forum->path]));

        if ($request->old('attachments')) {
            $attachments = app(AttachmentRepositoryInterface::class)->findByFile(request()->old('attachments'));
        }

        if (auth()->check()) {
            // default subscribe setting
            $subscribe = auth()->user()->allow_subscribe;
        }

        return Controller::view('forum.submit')->with(compact('forum', 'attachments', 'subscribe'));
    }

    /**
     * @param \Coyote\Forum $forum
     * @param PostRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save($forum, PostRequest $request)
    {
        $url = \DB::transaction(function () use ($request, $forum) {
            // create new topic
            $topic = $this->topic->create($request->all() + ['forum_id' => $forum->id]);
            // create new post and assign it to topic. don't worry about the rest: trigger will do the work
            $post = $this->post->create($request->all() + [
                'user_id'   => $this->userId,
                'topic_id'  => $topic->id,
                'forum_id'  => $forum->id,
                'ip'        => request()->ip(),
                'browser'   => request()->browser(),
                'host'      => request()->server('SERVER_NAME')
            ]);

            $tags = $request->get('tags', []);

            // assign tags to topic
            $topic->setTags($tags);

            // assign attachments to the post
            $post->setAttachments($request->get('attachments', []));

            // save it in log...
            (new Log())
                ->setPost($post)
                ->fill(['subject' => $topic->subject, 'tags' => $tags])
                ->save();

            if (auth()->check()) {
                $topic->subscribe($this->userId, $request->get('subscribe'));
                // automatically subscribe post
                $post->subscribe($this->userId, true);
            }

            // parsing text and store it in cache
            // it's important. don't remove below line so that text in activity can be saved without markdown
            $post->text = app()->make('Parser\Post')->parse($request->text);

            // get id of users that were mentioned in the text
            $usersId = $forum->onlyUsersWithAccess((new Ref_Login())->grab($post->text));

            if ($usersId) {
                app()->make('Alert\Post\Login')->with([
                    'users_id'    => $usersId,
                    'sender_id'   => $this->userId,
                    'sender_name' => $request->get('user_name', auth()->user()->name),
                    'subject'     => excerpt($request->subject),
                    'excerpt'     => excerpt($post->text),
                    'url'         => route('forum.topic', [$forum->path, $topic->id, $topic->path], false)
                ])->notify();
            }

            $actor = new Stream_Actor(auth()->user());

            if (auth()->guest()) {
                $actor->displayName = $request->get('user_name');
            }
            app()->make('Stream')->add(
                new Stream_Create(
                    $actor,
                    (new Stream_Topic)->map($topic, $forum, $post->text),
                    (new Stream_Forum)->map($forum)
                )
            );

            // fire the event. it can be used to index a content and/or add page path to "pages" table
            event(new TopicWasSaved($topic));
            // add post to elasticsearch
            event(new PostWasSaved($post));

            return route('forum.topic', [$forum->path, $topic->id, $topic->path]);
        });

        return redirect()->to($url);
    }

    /**
     * @param Topic $topic
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
     * @param \Coyote\Topic $topic
     * @param SubjectRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function subject($topic, SubjectRequest $request)
    {
        $forum = $topic->forum()->first();
        $this->authorize('update', $forum);

        $url = \DB::transaction(function () use ($request, $forum, $topic) {
            $topic->fill(['subject' => $request->get('subject')]);

            $post = $this->post->find($topic->first_post_id);
            $url = route('forum.topic', [$forum->path, $topic->id, $topic->path], false);

            if ($topic->isDirty()) {
                $original = $topic->getOriginal();

                $topic->save();
                $tags = $topic->getTagNames();

                // save it in log...
                (new Log)
                    ->setPost($post)
                    ->fill(['user_id' => $this->userId, 'subject' => $topic->subject, 'tags' => $tags])
                    ->save();

                $post->fill([
                    'edit_count' => $post->edit_count + 1, 'editor_id' => $this->userId
                ])
                ->save();

                if ($post->user_id) {
                    app()->make('Alert\Topic\Subject')->with([
                        'users_id'    => $forum->onlyUsersWithAccess([$post->user_id]),
                        'sender_id'   => $this->userId,
                        'sender_name' => auth()->user()->name,
                        'subject'     => excerpt($original['subject']),
                        'excerpt'     => excerpt($topic->subject),
                        'url'         => $url
                    ])->notify();
                }

                // fire the event. it can be used to index a content and/or add page path to "pages" table
                event(new TopicWasSaved($topic));
                // add post to elasticsearch
                event(new PostWasSaved($post));
            }

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
