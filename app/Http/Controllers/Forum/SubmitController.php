<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Forum;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Forms\Forum\SubjectForm;
use Coyote\Http\Requests\Forum\PostRequest;
use Coyote\Http\Resources\PostResource;
use Coyote\Notifications\Post\ChangedNotification;
use Coyote\Notifications\Post\SubmittedNotification;
use Coyote\Notifications\Post\UserMentionedNotification;
use Coyote\Notifications\Topic\SubjectChangedNotification;
use Coyote\Post;
use Coyote\Repositories\Contracts\PollRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\Topic;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Http\Request;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Services\Stream\Objects\Post as Stream_Post;
use Coyote\Services\Stream\Objects\Forum as Stream_Forum;
use Coyote\Services\Stream\Actor as Stream_Actor;
use Coyote\Services\Parser\Helpers\Login as LoginHelper;
use Coyote\Events\PostWasSaved;
use Coyote\Events\TopicWasSaved;
use Coyote\Post\Log;

class SubmitController extends BaseController
{
    /**
     * Show new post/edit form
     *
     * @param Request $request
     * @param \Coyote\Forum $forum
     * @param \Coyote\Topic $topic
     * @param \Coyote\Post|null $post
     * @return \Illuminate\View\View
     */
    public function index($forum)
    {
        $this->breadcrumb->push('Nowy wątek', route('forum.topic.submit', [$forum->slug]));

        return Controller::view('forum.submit', [
            'forum' => $forum,
            'show_sticky_checkbox' => $this->userId ? $this->auth->can('sticky', $forum) : false
        ]);
    }

    /**
     * @param PostRequest $request
     * @param Forum $forum
     * @param Topic|null $topic
     * @param Post|null $post
     * @return PostResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function save(PostRequest $request, Forum $forum, ?Topic $topic, ?Post $post)
    {
        if (!$topic->exists) {
            $topic = $this->topic->makeModel();
            $topic->forum()->associate($forum);
        }

        $topic->fill($request->only(array_keys($request->rules())));

        if (!$post->exists) {
            $post->forum()->associate($forum);

            if ($this->userId) {
                $post->user()->associate($this->auth);
            }

            $post->ip = $request->ip();
            $post->browser = '';
//            $post->browser = str_limit($this->request->browser(), 250);
            $post->host = ''; // pole nie moze byc nullem
        } else {
            $this->authorize('update', [$post]);

            $post->topic()->associate($topic);
        }

        $post->fill($request->all());

        if ($post->isDirtyWithRelations() && $post->exists) {
            $post->fill([
                'edit_count' => $post->edit_count + 1, 'editor_id' => $this->auth->id
            ]);
        }

        $post = $this->transaction(function () use ($forum, $topic, $post, $request) {
            $actor = new Stream_Actor($this->auth);

            if (auth()->guest()) {
                $actor->displayName = $request->get('user_name');
            }

//            $poll = $this->savePoll($request, $topic->poll_id);

            $activity = $post->id ? new Stream_Update($actor) : new Stream_Create($actor);

            $topic->save();

            $post->topic()->associate($topic);
            $post->save();

            if ($topic->wasRecentlyCreated && $this->userId) {
                $topic->subscribe($this->userId, $request->filled('is_subscribed'));
            }

            // url to the post
            $url = UrlBuilder::post($post);

            if ($topic->wasRecentlyCreated || $post->id === $topic->first_post_id) {
                $object = (new Stream_Topic)->map($topic, $post->html);
                $target = (new Stream_Forum)->map($forum);
            } else {
                $object = (new Stream_Post(['url' => $url]))->map($post);
                $target = (new Stream_Topic())->map($topic);
            }

            stream($activity, $object, $target);

            return $post;
        });

        // fire the event. it can be used to index a content and/or add page path to "pages" table
        event(new TopicWasSaved($topic));
        // add post to elasticsearch
        event(new PostWasSaved($post));

        $tracker = Tracker::make($topic);

        PostResource::withoutWrapping();

        if ($post->user->group) {
            $post->user->group = $post->user->group->name;
        }

        return (new PostResource($post))->setTracker($tracker)->setSigParser(app('parser.sig'));
    }

    /**
     * @param Request $request
     * @param int $pollId
     * @return \Coyote\Poll|null
     */
    private function savePoll(Request $request, $pollId)
    {
        if ($request->input('poll.remove')) {
            $this->getPollRepository()->delete($pollId);
        } elseif ($request->filled('poll.title')) {
            return $this->getPollRepository()->updateOrCreate($pollId, $request->input('poll'));
        } elseif ($pollId) {
            return $this->getPollRepository()->find($pollId);
        }

        return null;
    }

    /**
     * @return PollRepositoryInterface
     */
    private function getPollRepository()
    {
        return app(PollRepositoryInterface::class);
    }

    /**
     * @param \Coyote\Topic $topic
     * @param SubjectForm $form
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @todo moze jakas refaktoryzacja? przeniesienie do repozytorium? na pewno logowanie o tym, ze zostal zmieniony
     * tytul a nie tresc posta (jak to jest obecnie)
     */
    public function subject($topic, SubjectForm $form)
    {
        /** @var \Coyote\Forum $forum */
        $forum = $topic->forum()->first();
        $this->authorize('update', $forum);

        $request = $form->getRequest();

        $url = $this->transaction(function () use ($request, $forum, $topic) {
            $topic->fill(['subject' => $request->get('subject')]);

            /** @var \Coyote\Post $post */
            $post = $topic->firstPost()->first();
            $url = route('forum.topic', [$forum->slug, $topic->id, $topic->slug], false);

            if ($topic->isDirty()) {
                $original = $topic->getOriginal();

                $topic->save();
                $tags = $topic->getTagNames();

                // save it in log...
                (new Log)
                    ->fillWithPost($post)
                    ->fill(['user_id' => $this->userId, 'subject' => $topic->subject, 'tags' => $tags])
                    ->save();

                $post->fill([
                    'edit_count' => $post->edit_count + 1, 'editor_id' => $this->userId
                ])
                ->save();

                if ($post->user_id !== null) {
                    $post->user->notify(
                        (new SubjectChangedNotification($this->auth, $topic))
                            ->setOriginalSubject(str_limit($original['subject'], 84))
                    );
                }

                // fire the event. it can be used to index a content and/or add page path to "pages" table
                event(new TopicWasSaved($topic));
                // add post to elasticsearch
                event(new PostWasSaved($post));
            }

            // get text from cache to put excerpt in stream activity
            $post->text = app('parser.post')->parse($post->text);

            // put action into activity stream
            stream(
                Stream_Update::class,
                (new Stream_Topic)->map($topic, $post->text),
                (new Stream_Forum)->map($forum)
            );

            return $url;
        });

        if ($request->ajax()) {
            return response(url($url));
        } else {
            return redirect()->to($url);
        }
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function preview(Request $request)
    {
        $parser = app('parser.post');
        $parser->cache->setEnable(false);

        return response($parser->parse((string) $request->get('text')));
    }
}
