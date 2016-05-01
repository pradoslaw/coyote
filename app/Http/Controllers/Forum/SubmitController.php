<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Forms\Forum\SubjectForm;
use Coyote\Repositories\Contracts\PollRepositoryInterface;
use Illuminate\Http\Request;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Services\Stream\Objects\Post as Stream_Post;
use Coyote\Services\Stream\Objects\Forum as Stream_Forum;
use Coyote\Services\Stream\Actor as Stream_Actor;
use Coyote\Services\Parser\Reference\Login as Ref_Login;
use Coyote\Events\PostWasSaved;
use Coyote\Events\TopicWasSaved;
use Coyote\Services\Alert\Alert;
use Coyote\Post\Log;
use Coyote\User;

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
    public function index(Request $request, $forum, $topic, $post = null)
    {
        $this->breadcrumb($forum);

        if (!empty($topic->id)) {
            $this->breadcrumb->push($topic->subject, route('forum.topic', [$forum->path, $topic->id, $topic->path]));

            if ($post === null) {
                $this->breadcrumb->push('Odpowiedz', url($request->path()));
            } else {
                $this->breadcrumb->push('Edycja', url($request->path()));
            }
        } else {
            $this->breadcrumb->push('Nowy wątek', route('forum.topic.submit', [$forum->path]));
        }

        if (!empty($post)) {
            // make sure user can edit this post
            $this->authorize('update', [$post, $forum]);
        }

        $form = $this->getForm($forum, $topic, $post);
        $form->text->setValue($form->text->getValue() ?: ($topic ? $this->getDefaultText($request, $topic) : ''));

        return Controller::view('forum.submit')->with(compact('forum', 'form', 'topic', 'post'));
    }

    /**
     * Show new post/edit form
     *
     * @param \Coyote\Forum $forum
     * @param \Coyote\Topic $topic
     * @param \Coyote\Post|null $post
     * @return mixed
     */
    public function save($forum, $topic, $post = null)
    {
        if (is_null($post)) {
            $post = $this->post->makeModel();
        }

        $form = $this->getForm($forum, $topic, $post);
        $form->validate();

        $request = $form->getRequest();

        $url = \DB::transaction(function () use ($request, $forum, $topic, $post) {
            $actor = new Stream_Actor(auth()->user());
            if (auth()->guest()) {
                $actor->displayName = $request->get('user_name');
            }

            $poll = $this->savePoll($request, $topic->id);

            $activity = $post->id ? new Stream_Update($actor) : new Stream_Create($actor);
            // saving post through repository... we need to pass few object to save relationships
            $this->post->save($request, auth()->user(), $forum, $topic, $post, $poll);

            // url to the post
            $url = route('forum.topic', [$forum->path, $topic->id, $topic->path], false) . '?p=' . $post->id . '#id' . $post->id;

            // parsing text and store it in cache
            // it's important. don't remove below line so that text in activity can be saved without markdown
            $post->text = app('Parser\Post')->parse($request->text);

            $alert = new Alert();
            $notification = [
                'sender_id'   => $this->userId,
                'sender_name' => $request->get('user_name', $this->userId ? auth()->user()->name : ''),
                'subject'     => excerpt($topic->subject),
                'excerpt'     => excerpt($post->text),
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
            $subscribersId = $forum->onlyUsersWithAccess((new Ref_Login())->grab($post->text));
            if ($subscribersId) {
                $alert->attach(app()->make('Alert\Post\Login')->with($notification)->setUsersId($subscribersId));
            }

            $alert->notify();

            if ($post->id === $topic->first_post_id) {
                $object = (new Stream_Topic)->map($topic, $forum, $post->text);
                $target = (new Stream_Forum)->map($forum);
            } else {
                $object = (new Stream_Post(['url' => $url]))->map($post);
                $target = (new Stream_Topic())->map($topic, $forum);
            }

            stream($activity, $object, $target);

            // fire the event. it can be used to index a content and/or add page path to "pages" table
            event(new TopicWasSaved($topic));
            // add post to elasticsearch
            event(new PostWasSaved($post));

            return $url;
        });

        // is this a quick edit (via ajax)?
        // @todo to nie powinno sie raczej tu znajdowac. przeniesc do middleware?
        if ($request->ajax()) {
            $data = ['post' => ['text' => $post->text, 'attachments' => $post->attachments()->get()]];

            if (auth()->user()->allow_sig && $post->user_id) {
                $parser = app('Parser\Sig');
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
     * @param Request $request
     * @param int $pollId
     * @return \Coyote\Poll|null
     */
    private function savePoll(Request $request, $pollId)
    {
        if ($request->has('poll.title')) {
            return $this->getPollRepository()->updateOrCreate($pollId, $request->get('poll'));
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
     * Ajax request. Display edit form
     *
     * @param $forum
     * @param $topic
     * @param $post
     * @return $this
     */
    public function edit($forum, $topic, $post)
    {
        $form = $this->getForm($forum, $topic, $post);

        return view('forum.partials.edit')->with(compact('post', 'forum', 'topic', 'attachments', 'form'));
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
        $forum = $topic->forum()->first();
        $this->authorize('update', $forum);

        $request = $form->getRequest();

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
                    ->fillWithPost($post)
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

    /**
     * Format post text in case of quoting
     *
     * @param Request $request
     * @param \Coyote\Topic $topic
     * @return string
     */
    protected function getDefaultText(Request $request, $topic)
    {
        $text = '';

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

        if (!empty($postsId)) {
            $posts = $this->post->findPosts(array_unique($postsId), $topic->id);

            // builds text with quoted posts
            foreach ($posts as $post) {
                $text .= '> ##### [' .
                    ($post->name ?: $post->user_name) .
                    ' napisał(a)](' . route('forum.share', [$post->id]) . '):';

                $text .= "\n> " . str_replace("\n", "\n> ", $post->text);
                $text .= "\n\n";
            }
        }

        return $text;
    }
}
