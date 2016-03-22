<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Alert\Alert;
use Coyote\Events\PostWasDeleted;
use Coyote\Events\PostWasSaved;
use Coyote\Events\TopicWasDeleted;
use Coyote\Events\TopicWasSaved;
use Coyote\Forum\Reason;
use Coyote\Http\Requests\PostRequest;
use Coyote\Post\Log;
use Coyote\Repositories\Contracts\FlagRepositoryInterface as Flag;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Contracts\Post\AttachmentRepositoryInterface as Attachment;
use Coyote\Repositories\Contracts\PostRepositoryInterface as Post;
use Coyote\Repositories\Contracts\TagRepositoryInterface;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Coyote\Parser\Reference\Login as Ref_Login;
use Coyote\Stream\Activities\Create as Stream_Create;
use Coyote\Stream\Activities\Update as Stream_Update;
use Coyote\Stream\Activities\Delete as Stream_Delete;
use Coyote\Stream\Activities\Restore as Stream_Restore;
use Coyote\Stream\Activities\Vote as Stream_Vote;
use Coyote\Stream\Activities\Accept as Stream_Accept;
use Coyote\Stream\Activities\Reject as Stream_Reject;
use Coyote\Stream\Objects\Topic as Stream_Topic;
use Coyote\Stream\Objects\Post as Stream_Post;
use Coyote\Stream\Objects\Forum as Stream_Forum;
use Coyote\Stream\Actor as Stream_Actor;
use Coyote\User;
use Gate;
use Illuminate\Http\Request;

class PostController extends BaseController
{
    /**
     * @var Post
     */
    private $post;

    /**
     * @var Attachment
     */
    private $attachment;

    /**
     * @param Forum $forum
     * @param Topic $topic
     * @param Post $post
     * @param Attachment $attachment
     */
    public function __construct(Forum $forum, Topic $topic, Post $post, Attachment $attachment)
    {
        parent::__construct($forum, $topic);

        $this->post = $post;
        $this->attachment = $attachment;
    }

    /**
     * Show new post/edit form
     *
     * @param Forum $forum
     * @param Topic $topic
     * @param Post|null $post
     * @return mixed
     */
    public function submit($forum, $topic, $post = null)
    {
        $this->breadcrumb($forum);
        $this->breadcrumb->push($topic->subject, route('forum.topic', [$forum->path, $topic->id, $topic->path]));

        // list of post's attachments
        $attachments = collect();

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

            $text = $post->text; // we're gonna pass this variable to the view
            $attachments = $attachments->merge($post->attachments()->get());
        }

        if (auth()->check()) {
            $subscribe = $topic->subscribers()->where('user_id', $this->userId)->count();

            // we're creating new post...
            if ($post === null && $subscribe == false && auth()->user()->allow_subscribe) {
                // if this is the first post in this topic, subscribe option depends on user's default setting
                if (!$topic->users()->where('user_id', $this->userId)->count()) {
                    $subscribe = true;
                }
            }
        }

        // IDs of posts that user want to quote...
        $postsId = [];
        $cookie = isset($_COOKIE['mqid' . $topic->id]) ? $_COOKIE['mqid' . $topic->id] : null;

        if ($cookie) {
            $postsId = array_map('intval', explode(',', $cookie));
            // I used raw PHP function because I don't want to use laravel encryption in this case
            setcookie('mqid' . $topic->id, null, time() - 3600, '/');
        }

        if (request()->input('quote')) {
            $postsId[] = request()->input('quote');
        }

        if ($postsId) {
            $posts = $this->post->findPosts(array_unique($postsId), $topic->id);
            $body = '';

            // builds text with quoted posts
            foreach ($posts as $post) {
                $body .= '> ##### [' .
                    ($post->name ?: $post->user_name) .
                    ' napisał(a)](' . route('forum.share', [$post->id]) . '):';

                $body .= "\n> " . str_replace("\n", "\n> ", $post->text);
                $body .= "\n\n";
            }

            unset($post); // <-- delete this variable. we don't want to pass it to twig
            $text = $body;
        }

        if (request()->old('attachments')) {
            $attachments = $this->attachment->findByFile(request()->old('attachments'));
        }

        return $this->view('forum.submit')->with(
            compact('forum', 'topic', 'post', 'text', 'title', 'tags', 'subscribe', 'attachments')
        );
    }

    /**
     * Ajax request. Display edit form
     *
     * @param $forum
     * @param $topic
     * @param $post
     * @return $this
     */
    public function edit($forum, $topic, $post)
    {
        if ($post->id === $topic->first_post_id) {
            // get topic tags only if this post is the FIRST post in topic
            $tags = $topic->getTagNames();
        }
        $subscribe = $topic->subscribers()->forUser($this->userId)->count();
        $attachments = $post->attachments()->get();

        return view('forum.partials.edit')->with(compact('post', 'forum', 'topic', 'tags', 'subscribe', 'attachments'));
    }

    /**
     * Save post (edit or create)
     *
     * @param PostRequest $request
     * @param \Coyote\Forum $forum
     * @param \Coyote\Topic $topic
     * @param \Coyote\Post|null $post
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(PostRequest $request, $forum, $topic, $post = null)
    {
        // parsing text and store it in cache
        $text = app()->make('Parser\Post')->parse($request->text);

        $url = \DB::transaction(function () use ($text, $request, $forum, $topic, $post) {
            $actor = new Stream_Actor(auth()->user());

            // url to the post
            $url = route('forum.topic', [$forum->path, $topic->id, $topic->path], false);

            // post has been modified...
            if ($post !== null) {
                $url .= '?p=' . $post->id . '#id' . $post->id;

                $this->authorize('update', [$post, $forum]);

                $data = $request->all();
                $post->fill($data);

                // $isDirty determines if post has been changed somehow (body, title or tags)
                $isDirty = $post->isDirty('text'); // <-- we only wanna know if text has changed...
                $activity = new Stream_Update($actor);

                $tags = $request->get('tags', []);
                $log = new Log();

                // user wants to change the subject. we must update "topics" table
                if ($post->id === $topic->first_post_id) {
                    $topic->fill($request->all());

                    // we only want to know if subject has changed. in that case we need to add record
                    // to log database
                    if ($topic->isDirty('subject')) {
                        $isDirty = true;
                    }

                    // every time we need to save this record. user might change other options
                    // like user name or sticky checkbox
                    $topic->save();

                    $current = $topic->getTagNames();
                    $log->subject = $topic->subject;
                    $log->tags = $tags;

                    if (array_merge(array_diff($tags, $current), array_diff($current, $tags))) {
                        // assign tags to topic
                        $topic->setTags($tags);
                        $isDirty = true;
                    }

                    // fire the event. it can be used to index a content and/or add page path to "pages" table
                    event(new TopicWasSaved($topic));
                }

                if ($isDirty) {
                    $post->fill([
                        'edit_count' => $post->edit_count + 1, 'editor_id' => $this->userId
                    ]);

                    $log->setPost($post);
                    $log->user_id = $this->userId; // it can be moderator action (moderator is editing post)
                    $log->save();
                }

                $post->save();
            } else {
                if (auth()->guest()) {
                    $actor->displayName = $request->get('user_name');
                }
                $activity = new Stream_Create($actor);

                // create new post and assign it to topic. don't worry about the rest: trigger will do the work
                $post = $this->post->create($request->all() + [
                    'user_id'   => $this->userId,
                    'topic_id'  => $topic->id,
                    'forum_id'  => $forum->id,
                    'ip'        => request()->ip(),
                    'browser'   => request()->browser(),
                    'host'      => request()->server('SERVER_NAME')
                ]);

                // automatically subscribe post
                if (auth()->check()) {
                    $post->subscribe($this->userId, true);
                }

                $url .= '?p=' . $post->id . '#id' . $post->id;

                $alert = new Alert();
                $notification = [
                    'sender_id'   => $this->userId,
                    'sender_name' => $request->get('user_name', $this->userId ? auth()->user()->name : ''),
                    'subject'     => excerpt($topic->subject),
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

                // initial history of post
                (new Log)->setPost($post)->save();
            }

            if (auth()->check() && $post->user_id) {
                $topic->subscribe($post->user_id, $request->get('subscribe'));
            }

            // assign attachments to the post
            $post->setAttachments($request->get('attachments', []));

            // it's important. don't remove below line so that text in activity can be saved without markdown
            $post->text = $text;

            $activity->setObject((new Stream_Post(['url' => $url]))->map($post));
            $activity->setTarget((new Stream_Topic())->map($topic, $forum));

            // put action into activity stream
            stream($activity);

            // add post to elasticsearch
            event(new PostWasSaved($post));

            return $url;
        });

        // is this a quick edit (via ajax)?
        if ($request->ajax()) {
            $data = ['post' => ['text' => $text, 'attachments' => $post->attachments()->get()]];

            if (auth()->user()->allow_sig && $post->user_id) {
                $parser = app()->make('Parser\Sig');
                $user = User::find($post->user_id, ['sig']);

                if ($user->sig) {
                    $data['post']['sig'] = $parser->parse($user->sig);
                }
            }
            return view('forum.partials.text', $data);
        }

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
        if (!$forum->userCanAccess($this->userId)) {
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
                'sender_id'   => $this->userId,
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
                    // delete topic's flag
                    app('FlagRepository')->deleteBy('topic_id', $topic->id);

                    if ($subscribersId) {
                        app()->make('Alert\Topic\Delete')
                            ->with($notification)
                            ->setUrl($url)
                            ->setUsersId($subscribersId->toArray())
                            ->notify();
                    }

                    // fire the event. it can be used to delete row from "pages" table or from search index
                    event(new TopicWasDeleted($topic));
                } else {
                    $activity = Stream_Restore::class;
                    $topic->restore();

                    event(new TopicWasSaved($topic));
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
                    // delete post's flags
                    app('FlagRepository')->deleteBy('post_id', $post->id);

                    if ($subscribersId) {
                        app()->make('Alert\Post\Delete')
                            ->with($notification)
                            ->setUrl($url)
                            ->setUsersId($subscribersId->toArray())
                            ->notify();
                    }

                    $redirect = back();
                    // fire the event. delete from search index
                    event(new PostWasDeleted($post));
                } else {
                    $activity = Stream_Restore::class;
                    $post->restore();
                    $redirect = redirect()->to($url);

                    // fire the event. add post to search engine
                    event(new PostWasSaved($post));
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
     * @param Post $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function subscribe($post)
    {
        $subscriber = $post->subscribers()->forUser($this->userId)->first();

        if ($subscriber) {
            $subscriber->delete();
        } else {
            $post->subscribers()->create(['user_id' => $this->userId]);
        }
    }

    /**
     * @param Post $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function vote($post)
    {
        if (auth()->guest()) {
            return response()->json(['error' => 'Musisz być zalogowany, aby oddać ten głos.'], 500);
        }

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

        \DB::transaction(function () use ($post, $topic, $forum) {
            $result = $post->votes()->forUser($this->userId)->first();

            // build url to post
            $url = route('forum.topic', [$forum->path, $topic->id, $topic->path], false) . '?p=' . $post->id . '#id' . $post->id;
            // excerpt of post text. used in reputation and alert
            $excerpt = excerpt($post->text);

            if ($result) {
                $result->delete();
                $post->score--;
            } else {
                $post->votes()->create([
                    'user_id' => $this->userId, 'forum_id' => $forum->id, 'ip' => request()->ip()
                ]);
                $post->score++;

                // send notification to the user
                app()->make('Alert\Post\Vote')
                    ->setPostId($post->id)
                    ->addUserId($post->user_id)
                    ->setSubject(excerpt($topic->subject, 48))
                    ->setExcerpt($excerpt)
                    ->setSenderId($this->userId)
                    ->setSenderName(auth()->user()->name)
                    ->setUrl($url)
                    ->notify();
            }

            // increase/decrease reputation points according to the forum settings
            if ($post->user_id && $forum->enable_reputation) {
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
            stream(Stream_Vote::class, (new Stream_Post(['url' => $url]))->map($post), (new Stream_Topic())->map($topic, $forum));
        });

        return response()->json(['count' => $post->score]);
    }

    /**
     * @param Post $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function accept($post)
    {
        if (auth()->guest()) {
            return response()->json(['error' => 'Musisz być zalogowany, aby zaakceptować ten post.'], 500);
        }

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
            && $this->post->find($topic->first_post_id, ['user_id'])->user_id !== $this->userId) {
            return response()->json(['error' => 'Możesz zaakceptować post tylko we własnym wątku.'], 500);
        }

        \DB::transaction(function () use ($topic, $post, $forum) {
            $result = $topic->accept()->where('topic_id', $topic->id)->first();

            // build url to post
            $url = route('forum.topic', [$forum->path, $topic->id, $topic->path], false);
            // excerpt of post text. used in reputation and alert
            $excerpt = excerpt($post->text);

            // add or subtract reputation points
            $reputation = app()->make('Reputation\Post\Accept');
            $target = (new Stream_Topic())->map($topic, $forum);

            // user might change his mind and accept different post (or he can uncheck solved post)
            if ($result) {
                $old = $this->post->find($result->post_id, ['user_id', 'text']);

                $reputation->setUrl($url . '?p=' . $result->post_id . '#id' . $result->post_id);
                $reputation->setExcerpt($excerpt);

                // add into activity stream
                stream(Stream_Reject::class, (new Stream_Post(['url' => $reputation->getUrl()]))->map($old), $target);

                // reverse reputation points
                if ($forum->enable_reputation) {
                    $reputation->setIsPositive(false)->setPostId($result->post_id);

                    if ($result->post_id !== $post->id) {
                        $reputation->setExcerpt(excerpt($old->text));

                        if ($old->user_id !== $result->user_id) {
                            $reputation->setUserId($old->user_id)->save();
                        }
                    } elseif ($result->user_id !== $post->user_id) {
                        // reverse reputation points for post author
                        $reputation->setUserId($post->user_id)->save(); // <-- don't change this. ($post->user_id)
                    }
                }

                $result->delete();
            }

            $reputation->setExcerpt($excerpt);
            $url .= '?p=' . $post->id . '#id' . $post->id;

            if (!$result || $post->id !== $result->post_id) {
                $reputation->setUrl($url);

                if ($post->user_id) {
                    // before we add reputation points we need to be sure that user does not accept his own post
                    if ($post->user_id !== $this->userId) {
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
                            ->setSenderId($this->userId)
                            ->setSenderName(auth()->user()->name)
                            ->setUrl($url)
                            ->notify();
                    }
                }

                $topic->accept()->create([
                    'post_id'   => $post->id,
                    'user_id'   => $this->userId, // don't change this. we need to know who accepted this post
                    'ip'        => request()->ip()
                ]);

                // add into activity stream
                stream(Stream_Accept::class, (new Stream_Post(['url' => $url]))->map($post), $target);
            }
        });
    }
}
