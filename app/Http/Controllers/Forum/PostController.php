<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Requests\PostRequest;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Contracts\PostRepositoryInterface as Post;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Coyote\Parser\Reference\Login as Ref_Login;
use Coyote\Stream\Activities\Create as Stream_Create;
use Coyote\Stream\Activities\Update as Stream_Update;
use Coyote\Stream\Activities\Delete as Stream_Delete;
use Coyote\Stream\Activities\Restore as Stream_Restore;
use Coyote\Stream\Objects\Topic as Stream_Topic;
use Coyote\Stream\Objects\Post as Stream_Post;
use Coyote\Stream\Objects\Forum as Stream_Forum;
use Coyote\Stream\Actor as Stream_Actor;
use Gate;

class PostController extends Controller
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

    public function submit($forum, $topic, $post = null)
    {
        $this->breadcrumb($forum);
        $this->breadcrumb->push($topic->subject, route('forum.topic', [$forum->path, $topic->id, $topic->path]));

        if ($post === null) {
            $this->breadcrumb->push('Odpowiedz', request()->path());
        } else {
            // make sure user can edit this post
            $this->authorize('update', [$post, $forum]);
            $this->breadcrumb->push('Edycja', request()->path());

            if ($post->id === $topic->first_post_id) {
                // get topic tags only if this post is the FIRST post in topic
                $tags = $topic->tags->pluck('name')->toArray();
            }
        }

        if (auth()->check()) {
            $isSubscribe = $topic->subscribers()->where('user_id', auth()->id())->count();
        }

        return parent::view('forum.submit')->with(compact('forum', 'topic', 'post', 'title', 'tags', 'isSubscribe'));
    }

    public function save(PostRequest $request, $forum, $topic, $post = null)
    {
        $url = \DB::transaction(function () use ($request, $forum, $topic, $post) {
            // parsing text and store it in cache
            $text = app()->make('Parser\Post')->parse($request->text);
            $actor = new Stream_Actor(auth()->user());

            // post has been modified...
            if ($post !== null) {
                $this->authorize('update', [$post, $forum]);
                $data = $request->only(['text', 'user_name']) + [
                        'edit_count' => $post->edit_count + 1, 'editor_id' => auth()->id()
                    ];

                $post->fill($data)->save();
                $activity = new Stream_Update($actor);

                // user want to change the subject. we must update topics table
                if ($post->id === $topic->first_post_id) {
                    $path = str_slug($request->get('subject'), '_');

                    $topic->fill($request->all() + ['path' => $path])->save();
                    $this->topic->setTags($topic->id, $request->get('tag', []));
                }
            } else {
                if (auth()->guest()) {
                    $actor->displayName = $request->get('user_name');
                }
                $activity = new Stream_Create($actor);

                // create new post and assign it to topic. don't worry about the rest: trigger will do the work
                $post = $this->post->create($request->all() + [
                    'user_id'   => auth()->id(),
                    'topic_id'  => $topic->id,
                    'forum_id'  => $forum->id,
                    'ip'        => request()->ip(),
                    'browser'   => request()->browser(),
                    'host'      => request()->server('SERVER_NAME')
                ]);

                // get id of users that were mentioned in the text
                $usersId = (new Ref_Login())->grab($text);

                if ($usersId) {
                    app()->make('Alert\Post\Login')->with([
                        'users_id'    => $usersId,
                        'sender_id'   => auth()->id(),
                        'sender_name' => $request->get('user_name', auth()->user()->name),
                        'subject'     => excerpt($topic->subject, 48),
                        'excerpt'     => excerpt($text),
                        'url'         => route('forum.topic', [$forum->path, $topic->id, $topic->path], false)
                    ])->notify();
                }
            }

            if (auth()->check() && $post->user_id) {
                $this->topic->subscribe($topic->id, $post->user_id, $request->get('subscribe'));
            }

            $url = route('forum.topic', [$forum->path, $topic->id, $topic->path], false);
            $url .= '?p=' . $post->id . '#id' . $post->id;

            $activity->setObject((new Stream_Post(['url' => $url]))->map($post));
            $activity->setTarget((new Stream_Topic())->map($topic, $forum));

            // put action into activity stream
            stream($activity);

            return $url;
        });

        return redirect()->to($url);
    }

    /**
     * Delete post or whole thread
     *
     * @param int $id post id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        // Step 1. Does post really exist?
        $post = $this->post->withTrashed()->findOrFail($id);
        $forum = $this->forum->find($post->forum_id);

        // Step 2. Does user really have permission to delete this post?
        $this->authorize('delete', [$post, $forum]);

        // Step 3. Maybe user does not have an access to this category?
        if (!$forum->userCanAccess(auth()->user())) {
            abort(401, 'Unauthorized');
        }

        $topic = $this->topic->withTrashed()->find($post->topic_id);

        // Step 4. Only moderators can delete this post if topic (or forum) was locked
        if (Gate::denies('delete', $forum)) {
            if ($topic->is_locked || $forum->is_locked || $topic->last_post_id > $post->id || !$post->deleted_at) {
                abort(401, 'Unauthorized');
            }
        }

        $url = \DB::transaction(function () use ($post, $topic, $forum) {
            // if this is the first post in topic... we must delete whole thread
            if ($post->id === $topic->first_post_id) {
                if (is_null($topic->deleted_at)) {
                    $activity = Stream_Delete::class;
                    $topic->delete();
                    $redirect = redirect()->route('forum.category', [$forum->path]);
                } else {
                    $activity = Stream_Restore::class;
                    $topic->restore();
                    $redirect = redirecT()->route('forum.topic', [$forum->path, $topic->id, $topic->path]);
                }

                $object = (new Stream_Topic())->map($topic, $forum);
                $target = (new Stream_Forum())->map($forum);
            } else {
                if (is_null($post->deleted_at)) {
                    $activity = Stream_Delete::class;
                    $post->delete();
                } else {
                    $activity = Stream_Restore::class;
                    $post->restore();
                }

                $redirect = back();

                // build url to post
                $url = route('forum.topic', [$forum->path, $topic->id, $topic->path], false);
                $url .= '?p=' . $post->id . '#id' . $post->id;

                $object = (new Stream_Post(['url' => $url]))->map($post);
                $target = (new Stream_Topic())->map($topic, $forum);
            }

            stream($activity, $object, $target);
            return $redirect->with('success', 'Operacja zakończona sukcesem.');
        });

        return $url;
    }
}
