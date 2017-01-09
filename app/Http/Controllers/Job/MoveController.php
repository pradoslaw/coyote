<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Events\JobDeleting;
use Coyote\Events\PostWasSaved;
use Coyote\Events\TopicWasSaved;
use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Contracts\PostRepositoryInterface as PostRepository;
use Coyote\Repositories\Contracts\StreamRepositoryInterface;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as TopicRepository;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Illuminate\Http\Request;
use Coyote\Job;
use Coyote\Services\Stream\Activities\Move as Stream_Move;
use Coyote\Services\Stream\Objects\Job as Stream_Job;
use Coyote\Services\Stream\Objects\Forum as Stream_Forum;

class MoveController extends Controller
{
    /**
     * @var PostRepository
     */
    private $post;

    /**
     * @param PostRepository $post
     */
    public function __construct(PostRepository $post)
    {
        parent::__construct();

        $this->post = $post;
    }

    /**
     * @param Job $job
     * @return \Illuminate\View\View
     */
    public function index(Job $job)
    {
        $forum = app(ForumRepository::class);

        $this->breadcrumb->push([
            'Praca' => route('job.home'),
            $job->title => route('job.offer', [$job->id, $job->slug]),
            'Przenieś ofertę pracy' => ''
        ]);

        return $this->view('job.move')->with([
            'forumList'         => $forum->choices('id'),
            'preferred'         => $forum->findBy('name', 'Ogłoszenia drobne', ['id']),
            'job'               => $job,
            'subscribed'        => $this->userId ? $job->subscribers()->forUser($this->userId)->exists() : false
        ]);
    }

    /**
     * @param Job $job
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function move(Job $job, Request $request)
    {
        $this->validate($request, [
            'forum_id' => 'required|integer|exists:forums,id'
        ]);

        $forum = app(ForumRepository::class)->find($request->input('forum_id'));
        /** @var \Coyote\Topic $topic */
        $topic = app(TopicRepository::class)->newInstance();
        /** @var \Coyote\Post $post */
        $post = app(PostRepository::class)->newInstance();

        $topic->fill(['subject' => $job->title]);
        $topic->forum()->associate($forum);

        /** @var StreamRepositoryInterface $stream */
        $stream = app(StreamRepositoryInterface::class);

        $log = $stream->findWhere(['object.objectType' => 'job', 'object.id' => $job->id, 'verb' => 'create'])->first();

        $post->forceFill([
            'user_id'   => $job->user_id,
            'text'      => $job->description,
            'ip'        => $log->ip,
            'browser'   => $log->browser,
            'host'      => gethostbyaddr($log->ip)
        ]);

        $this->transaction(function () use ($request, $job, $forum, $topic, $post) {
            $topic->save();

            $post->forum()->associate($forum);
            $post->topic()->associate($topic);

            $post->save();

            if ($job->user_id !== $job->user->id) {
                $post->subscribers()->create(['user_id' => $job->user_id]);
            }

            $log = new \Coyote\Post\Log();
            $log->fillWithPost($post)->fill(['subject' => $topic->subject]);

            event(new JobDeleting($job));

            $job->delete();

            // fire the event. it can be used to index a content and/or add page path to "pages" table
            event(new TopicWasSaved($topic));
            // add post to elasticsearch
            event(new PostWasSaved($post));

            stream(Stream_Move::class, (new Stream_Job())->map($job), (new Stream_Forum())->map($forum));
        });

        return redirect()
            ->to(UrlBuilder::post($post))
            ->with('success', 'Ogłoszenie zostało przeniesione.');
    }
}
