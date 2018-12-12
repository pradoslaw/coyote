<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Events\JobDeleting;
use Coyote\Events\PostWasSaved;
use Coyote\Events\TopicWasSaved;
use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Contracts\PostRepositoryInterface as PostRepository;
use Coyote\Repositories\Contracts\StreamRepositoryInterface as StreamRepository;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as TopicRepository;
use Coyote\Services\Forum\TreeBuilder;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Illuminate\Http\Request;
use Coyote\Job;
use Coyote\Services\Stream\Activities\Move as Stream_Move;
use Coyote\Services\Stream\Objects\Job as Stream_Job;
use Coyote\Services\Stream\Objects\Forum as Stream_Forum;

class MoveController extends Controller
{
    /**
     * @var ForumRepository
     */
    private $forum;

    /**
     * @var TopicRepository
     */
    private $topic;

    /**
     * @var PostRepository
     */
    private $post;

    /**
     * @var StreamRepository
     */
    private $stream;

    /**
     * @param ForumRepository $forum
     * @param TopicRepository $topic
     * @param PostRepository $post
     * @param StreamRepository $stream
     */
    public function __construct(
        ForumRepository $forum,
        TopicRepository $topic,
        PostRepository $post,
        StreamRepository $stream
    ) {
        parent::__construct();

        $this->forum = $forum;
        $this->topic = $topic;
        $this->post = $post;
        $this->stream = $stream;
    }

    /**
     * @param Job $job
     * @return \Illuminate\View\View
     */
    public function index($job)
    {
        $this->breadcrumb->push([
            'Praca' => route('job.home'),
            $job->title => route('job.offer', [$job->id, $job->slug]),
            'Przenieś ofertę pracy' => ''
        ]);

        $treeBuilder = new TreeBuilder();

        return $this->view('job.move')->with([
            'forumList'         => $treeBuilder->listById($this->forum->list()),
            'preferred'         => $this->forum->findBy('name', 'Ogłoszenia drobne', ['id']),
            'job'               => $job,
            'subscribed'        => $this->userId ? $job->subscribers()->forUser($this->userId)->exists() : false
        ]);
    }

    /**
     * @param Job $job
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function move($job, Request $request)
    {
        $this->validate($request, [
            'forum_id' => 'required|integer|exists:forums,id'
        ]);

        $forum = $this->forum->find($request->input('forum_id'));
        /** @var \Coyote\Topic $topic */
        $topic = $this->topic->newInstance();
        /** @var \Coyote\Post $post */
        $post = $this->post->newInstance();

        $topic->fill(['subject' => $job->title]);
        $topic->forum()->associate($forum);

        $log = $this->stream->findWhere(['object.objectType' => 'job', 'object.id' => $job->id, 'verb' => 'create'])->first();

        $post->forceFill([
            'user_id'   => $job->user_id,
            'text'      => $job->description,
            'ip'        => $log->ip,
            'browser'   => $log->browser,
            'host'      => gethostbyaddr($log->ip)
        ]);

        $this->transaction(function () use ($job, $forum, $topic, $post) {
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

            stream(Stream_Move::class, (new Stream_Job())->map($job), (new Stream_Forum())->map($forum));
        });

        // fire the event. it can be used to index a content and/or add page path to "pages" table
        event(new TopicWasSaved($topic));
        // add post to elasticsearch
        event(new PostWasSaved($post));

        return redirect()
            ->to(UrlBuilder::post($post))
            ->with('success', 'Ogłoszenie zostało przeniesione.');
    }
}
