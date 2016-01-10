<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Alert\Alert;
use Coyote\Forum\Reason;
use Coyote\Http\Requests\PostRequest;
use Coyote\Post\Subscriber;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Contracts\Post\AcceptRepositoryInterface as Accept;
use Coyote\Repositories\Contracts\Post\VoteRepositoryInterface as Vote;
use Coyote\Repositories\Contracts\PostRepositoryInterface as Post;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Coyote\Parser\Reference\Login as Ref_Login;
use Coyote\Stream\Activities\Create as Stream_Create;
use Coyote\Stream\Activities\Update as Stream_Update;
use Coyote\Stream\Activities\Delete as Stream_Delete;
use Coyote\Stream\Activities\Restore as Stream_Restore;
use Coyote\Stream\Activities\Vote as Stream_Vote;
use Coyote\Stream\Activities\Accept as Stream_Accept;
use Coyote\Stream\Objects\Topic as Stream_Topic;
use Coyote\Stream\Objects\Post as Stream_Post;
use Coyote\Stream\Objects\Forum as Stream_Forum;
use Coyote\Stream\Actor as Stream_Actor;
use Gate;
use Illuminate\Http\Request;

class PostController extends BaseController
{
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
        parent::__construct($forum, $topic);

        $this->post = $post;
    }

    public function submit($forum, $topic, $post = null)
    {
        $this->breadcrumb($forum);
        $this->breadcrumb->push($topic->subject, route('forum.topic', [$forum->path, $topic->id, $topic->path]));

        if ($post === null) {
            $this->breadcrumb->push('Odpowiedz', url(request()->path()));
        } else {
            // make sure user can edit this post
            $this->authorize('update', [$post, $forum]);
            $this->breadcrumb->push('Edycja', url(request()->path()));

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

            // url to the post
            $url = route('forum.topic', [$forum->path, $topic->id, $topic->path], false);

            // post has been modified...
            if ($post !== null) {
                $url .= '?p=' . $post->id . '#id' . $post->id;

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

                // automatically subscribe post
                if (auth()->check()) {
                    $this->post->subscribe($post->id, auth()->id(), true);
                }

                $url .= '?p=' . $post->id . '#id' . $post->id;

                $alert = new Alert();
                $notification = [
                    'sender_id'   => auth()->id(),
                    'sender_name' => $request->get('user_name', auth()->id() ? auth()->user()->name : ''),
                    'subject'     => excerpt($topic->subject, 48),
                    'excerpt'     => excerpt($text),
                    'url'         => $url
                ];

                $subscribersId = $topic->subscribers()->lists('user_id');
                if ($subscribersId) {
                    $alert->attach(
                        // $subscribersId can be int or array. we need to cast to array type
                        app()->make('Alert\Topic\Subscriber')->with($notification)->setUsersId($subscribersId->toArray())
                    );
                }

                // get id of users that were mentioned in the text
                $subscribersId = (new Ref_Login())->grab($text);
                if ($subscribersId) {
                    $alert->attach(app()->make('Alert\Post\Login')->with($notification)->setUsersId($subscribersId));
                }

                $alert->notify();
            }

            if (auth()->check() && $post->user_id) {
                $this->topic->subscribe($topic->id, $post->user_id, $request->get('subscribe'));
            }

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
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id, Request $request)
    {
        // it must be like that. only if reason has been chosen, we need to validate it.
        if ($request->get('reason')) {
            $this->validate($request, ['reason' => 'int|exists:forum_reasons,id']);
        }

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
            if ($topic->is_locked || $forum->is_locked || $post->id < $topic->last_post_id || $post->deleted_at) {
                abort(401, 'Unauthorized');
            }
        }

        $url = \DB::transaction(function () use ($post, $topic, $forum, $request) {
            // build url to post
            $url = route('forum.topic', [$forum->path, $topic->id, $topic->path], false);

            $notification = [
                'sender_id'   => auth()->id(),
                'sender_name' => auth()->user()->name,
                'subject'     => excerpt($topic->subject, 48)
            ];

            $reason = null;

            if ($request->get('reason')) {
                $reason = Reason::find($request->get('reason'));

                $notification = array_merge($notification, [
                    'excerpt'       => $reason->name,
                    'reasonName'    => $reason->name,
                    'reasonText'    => $reason->description
                ]);
            }

            // if this is the first post in topic... we must delete whole thread
            if ($post->id === $topic->first_post_id) {
                if (is_null($topic->deleted_at)) {
                    $activity = Stream_Delete::class;
                    $redirect = redirect()->route('forum.category', [$forum->path]);

                    $subscribersId = $topic->subscribers()->lists('user_id');
                    if ($post->user_id !== null) {
                        $subscribersId[] = $post->user_id;
                    }

                    $topic->delete();

                    if ($subscribersId) {
                        app()->make('Alert\Topic\Delete')
                            ->with($notification)
                            ->setUrl($url)
                            ->setUsersId($subscribersId->toArray())
                            ->notify();
                    }
                } else {
                    $activity = Stream_Restore::class;
                    $topic->restore();
                    $redirect = redirect()->route('forum.topic', [$forum->path, $topic->id, $topic->path]);
                }

                $object = (new Stream_Topic())->map($topic, $forum);
                $target = (new Stream_Forum())->map($forum);
            } else {
                $url .= '?p=' . $post->id . '#id' . $post->id;

                if (is_null($post->deleted_at)) {
                    $activity = Stream_Delete::class;
                    $subscribersId = $post->subscribers()->lists('user_id');

                    if ($post->user_id !== null) {
                        $subscribersId[] = $post->user_id;
                    }

                    $post->delete();

                    if ($subscribersId) {
                        app()->make('Alert\Post\Delete')
                            ->with($notification)
                            ->setUrl($url)
                            ->setUsersId($subscribersId->toArray())
                            ->notify();
                    }

                    $redirect = back();
                } else {
                    $activity = Stream_Restore::class;
                    $post->restore();
                    $redirect = redirect()->to($url);
                }

                $object = (new Stream_Post(['url' => $url]))->map($post);
                $target = (new Stream_Topic())->map($topic, $forum);
            }

            if (!empty($reason)) {
                $object->reasonName = $reason->name;
            }

            stream($activity, $object, $target);
            return $redirect->with('success', 'Operacja zakończona sukcesem.');
        });

        return $url;
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function subscribe($id)
    {
        $subscriber = Subscriber::where('post_id', $id)->where('user_id', auth()->id())->first();

        if ($subscriber) {
            $subscriber->delete();
        } else {
            Subscriber::create(['post_id' => $id, 'user_id' => auth()->id()]);
        }
    }

    /**
     * @param $id
     * @param Vote $vote
     * @return \Illuminate\Http\JsonResponse
     */
    public function vote($id, Vote $vote)
    {
        if (auth()->guest()) {
            return response()->json(['error' => 'Musisz być zalogowany, aby oddać ten głos.'], 500);
        }

        $post = $this->post->findOrFail($id);

        if (!config('app.debug') && auth()->user()->id === $post->user_id) {
            return response()->json(['error' => 'Nie możesz głosować na wpisy swojego autorstwa.'], 500);
        }

        $forum = $this->forum->find($post->forum_id);
        if ($forum->is_locked) {
            return response()->json(['error' => 'Forum jest zablokowane.'], 500);
        }

        $topic = $this->topic->find($post->topic_id, ['id', 'path', 'subject', 'is_locked']);
        if ($topic->is_locked) {
            return response()->json(['error' => 'Wątek jest zablokowany.'], 500);
        }

        \DB::transaction(function () use ($post, $topic, $forum, $vote) {
            $result = $vote->findWhere(['post_id' => $post->id, 'user_id' => auth()->id()])->first();

            // build url to post
            $url = route('forum.topic', [$forum->path, $topic->id, $topic->path], false) . '?p=' . $post->id . '#id' . $post->id;
            // excerpt of post text. used in reputation and alert
            $excerpt = excerpt($post->text);

            if ($result) {
                $result->delete();
                $post->score--;
            } else {
                $vote->create([
                    'post_id' => $post->id, 'user_id' => auth()->id(), 'forum_id' => $forum->id, 'ip' => request()->ip()
                ]);
                $post->score++;

                // send notification to the user
                app()->make('Alert\Post\Vote')
                    ->setPostId($post->id)
                    ->addUserId($post->user_id)
                    ->setSubject(excerpt($topic->subject, 48))
                    ->setExcerpt($excerpt)
                    ->setSenderId(auth()->id())
                    ->setSenderName(auth()->user()->name)
                    ->setUrl($url)
                    ->notify();
            }

            if ($post->user_id) {
                // add or subtract reputation points
                app()->make('Reputation\Post\Vote')
                    ->setUserId($post->user_id)
                    ->setIsPositive(!count($result))
                    ->setUrl($url)
                    ->setPostId($post->id)
                    ->setExcerpt($excerpt)
                    ->save();
            }

            // add into activity stream
            stream(Stream_Vote::class, (new Stream_Post(['url' => $url]))->map($post));
        });

        return response()->json(['count' => $post->score]);
    }

    public function accept($id, Accept $accept)
    {
        if (auth()->guest()) {
            return response()->json(['error' => 'Musisz być zalogowany, aby zaakceptować ten post.'], 500);
        }

        // user wants to accept this post...
        $post = $this->post->findOrFail($id);
        // post belongs to this topic:
        $topic = $this->topic->find($post->topic_id, ['id', 'path', 'subject', 'first_post_id', 'is_locked']);

        if ($topic->is_locked) {
            return response()->json(['error' => 'Wątek jest zablokowany.'], 500);
        }

        $forum = $this->forum->find($post->forum_id);
        if ($forum->is_locked) {
            return response()->json(['error' => 'Forum jest zablokowane.'], 500);
        }

        if (Gate::denies('update', $forum)
            && $this->post->find($topic->first_post_id, ['user_id'])->user_id !== auth()->id()) {
            return response()->json(['error' => 'Możesz zaakceptować post tylko we własnym wątku.'], 500);
        }

        \DB::transaction(function () use ($accept, $topic, $post, $forum) {
            $result = $accept->findWhere(['topic_id' => $topic->id])->first();

            // build url to post
            $url = route('forum.topic', [$forum->path, $topic->id, $topic->path], false);
            // excerpt of post text. used in reputation and alert
            $excerpt = excerpt($post->text);

            // add or subtract reputation points
            $reputation = app()->make('Reputation\Post\Accept');

            // user might change his mind and accept different post (or he can uncheck solved post)
            if ($result) {
                $reputation->setUrl($url . '?p=' . $result->post_id . '#id' . $result->post_id);
                $reputation->setExcerpt($excerpt);

                // reverse reputation points
                if ($forum->enable_reputation) {
                    $reputation->setIsPositive(false)->setPostId($result->post_id);

                    if ($result->post_id !== $post->id) {
                        $old = $this->post->find($result->post_id, ['user_id', 'text']);
                        $reputation->setExcerpt(excerpt($old->text));

                        if ($old->user_id !== $result->user_id) {
                            $reputation->setUserId($old->user_id)->save();
                        }
                    } elseif ($result->user_id !== $post->user_id) {
                        // reverse reputation points for post author
                        $reputation->setUserId($post->user_id)->save(); // <-- don't change this. ($post->user_id)
                    }
                }

                $accept->delete($result->id);
            }

            $reputation->setExcerpt($excerpt);
            $url .= '?p=' . $post->id . '#id' . $post->id;

            if (!$result || $post->id !== $result->post_id) {
                $reputation->setUrl($url);

                if ($post->user_id) {
                    // before we add reputation points we need to be sure that user does not accept his own post
                    if ($post->user_id !== auth()->id()) {
                        if ($forum->enable_reputation) {
                            // increase reputation points for author
                            $reputation->setIsPositive(true)->setPostId($post->id)->setUserId($post->user_id)->save();
                        }

                        // send notification to the user
                        app()->make('Alert\Post\Accept')
                            ->setPostId($post->id)
                            ->addUserId($post->user_id)
                            ->setSubject(excerpt($topic->subject, 48))
                            ->setExcerpt($excerpt)
                            ->setSenderId(auth()->id())
                            ->setSenderName(auth()->user()->name)
                            ->setUrl($url)
                            ->notify();
                    }
                }

                $accept->create([
                    'post_id'   => $post->id,
                    'topic_id'  => $topic->id,
                    'user_id'   => auth()->id(), // don't change this. we need to know who accepted this post
                    'ip'        => request()->ip()
                ]);
            }

            // add into activity stream
            stream(Stream_Accept::class, (new Stream_Post(['url' => $url]))->map($post));
        });
    }
}
