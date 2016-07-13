<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Events\JobWasDeleted;
use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Contracts\PollRepositoryInterface as PollRepository;
use Coyote\Repositories\Contracts\PostRepositoryInterface as PostRepository;
use Coyote\Repositories\Contracts\StreamRepositoryInterface;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as TopicRepository;
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
     * @return $this
     */
    public function index(Job $job)
    {
        $forum = app(ForumRepository::class);

        $this->breadcrumb->push($job->title, route('job.offer', [$job->id, $job->slug]));
        $this->breadcrumb->push('Przenieś ofertę pracy');

        return $this->view('job.move')->with([
            'forumList'         => $forum->forumList('id'),
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

        $topic->subject = $job->title;
        $post->text = $job->description;

        $this->transaction(function () use ($request, $job, $forum, $topic, $post) {
            $poll = app(PollRepository::class)->newInstance();
            /** @var StreamRepositoryInterface $stream */
            $stream = app(StreamRepositoryInterface::class);

            $log = $stream->findWhere(['object.objectType' => 'job', 'object.id' => $job->id, 'verb' => 'create'])->first();
            $this->post->save($request, $job->user, $forum, $topic, $post, $poll);

            if ($job->user_id !== $job->user->id) {
                $post->subscribers()->create(['user_id' => $job->user_id]);
            }

            // ugly fix: set correct IP according to job offer's author
            $ip = ['ip' => $log->ip, 'browser' => $log->browser];

            $post->update($ip);
            $post->logs()->first()->update($ip);

            $job->delete();
            event(new JobWasDeleted($job));

            stream(Stream_Move::class, (new Stream_Job())->map($job), (new Stream_Forum())->map($forum));
        });

        return redirect()
            ->route('forum.topic', [$post->forum->slug, $post->topic->id, $post->topic->slug])
            ->with('success', 'Ogłoszenie zostało przeniesione.');
    }
}
