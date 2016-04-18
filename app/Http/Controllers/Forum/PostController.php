<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Alert\Alert;
use Coyote\Events\PostWasSaved;
use Coyote\Events\TopicWasSaved;
use Coyote\Http\Requests\PostRequest;
use Coyote\Post\Log;
use Coyote\Repositories\Contracts\Post\AttachmentRepositoryInterface;
use Coyote\Repositories\Contracts\PostRepositoryInterface as Post;
use Coyote\Parser\Reference\Login as Ref_Login;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Services\Stream\Objects\Post as Stream_Post;
use Coyote\Services\Stream\Actor as Stream_Actor;
use Coyote\User;
use Gate; // @todo uzyc factory
use Illuminate\Http\Request;

class PostController extends BaseController
{
    /**
     * Show new post/edit form
     *
     * @param Request $request
     * @param \Coyote\Forum $forum
     * @param \Coyote\Topic $topic
     * @param \Coyote\Post|null $post
     * @return mixed
     */
    public function submit(Request $request, $forum, $topic, $post = null)
    {
        $this->breadcrumb($forum);
        $this->breadcrumb->push($topic->subject, route('forum.topic', [$forum->path, $topic->id, $topic->path]));

        // list of post's attachments
        $attachments = collect();

        if ($post === null) {
            $this->breadcrumb->push('Odpowiedz', url($request->path()));
        } else {
            // make sure user can edit this post
            $this->authorize('update', [$post, $forum]);
            $this->breadcrumb->push('Edycja', url($request->path()));

            if ($post->id === $topic->first_post_id) {
                // get topic tags only if this post is the FIRST post in topic
                $tags = $topic->getTagNames();
            }

            $text = $post->text; // we're gonna pass this variable to the view
            $attachments = $attachments->merge($post->attachments()->get());
        }

        if (auth()->check()) {
            $subscribe = $topic->subscribers()->forUser($this->userId)->exists();

            // we're creating new post...
            if ($post === null && $subscribe == false && auth()->user()->allow_subscribe) {
                // if this is the first post in this topic, subscribe option depends on user's default setting
                if ($topic->users()->forUser($this->userId)->exists()) {
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

        if ($request->input('quote')) {
            $postsId[] = $request->input('quote');
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

        if ($request->old('attachments')) {
            $attachments = app(AttachmentRepositoryInterface::class)->findByFile($request->old('attachments'));
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

                $subscribersId = $forum->onlyUsersWithAccess($topic->subscribers()->lists('user_id')->toArray());
                if ($subscribersId) {
                    $alert->attach(
                        // $subscribersId can be int or array. we need to cast to array type
                        app()->make('Alert\Topic\Subscriber')->with($notification)->setUsersId($subscribersId)
                    );
                }

                // get id of users that were mentioned in the text
                $subscribersId = $forum->onlyUsersWithAccess((new Ref_Login())->grab($text));
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
}
