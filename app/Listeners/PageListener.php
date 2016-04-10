<?php

namespace Coyote\Listeners;

use Coyote\Events\MicroblogWasDeleted;
use Coyote\Events\MicroblogWasSaved;
use Coyote\Events\TopicWasSaved;
use Coyote\Events\TopicWasDeleted;
use Coyote\Events\ForumWasSaved;
use Coyote\Events\ForumWasDeleted;
use Coyote\Events\JobWasSaved;
use Coyote\Events\JobWasDeleted;
use Coyote\Microblog;
use Coyote\Job;
use Coyote\Topic;
use Coyote\Forum;
use Coyote\Repositories\Contracts\PageRepositoryInterface as Page;
use Illuminate\Contracts\Queue\ShouldQueue;

class PageListener implements ShouldQueue
{
    /**
     * @var Page
     */
    protected $page;

    /**
     * PageListener constructor.
     * @param Page $page
     */
    public function __construct(Page $page)
    {
        $this->page = $page;
    }

    /**
     * @param TopicWasSaved $event
     */
    public function onTopicSave(TopicWasSaved $event)
    {
        $event->topic->page()->updateOrCreate([
            'content_id' => $event->topic->id,
            'content_type' => Topic::class
        ], [
            'title' => $event->topic->subject,
            'path' => route('forum.topic', [$event->topic->forum->path, $event->topic->id, $event->topic->path], false)
        ]);
    }

    /**
     * @param TopicWasDeleted $event
     */
    public function onTopicDelete(TopicWasDeleted $event)
    {
        $this->page->findByContent($event->topic['id'], Topic::class)->delete();
    }

    /**
     * @param ForumWasSaved $event
     */
    public function onForumSave(ForumWasSaved $event)
    {
        $event->forum->page()->updateOrCreate([
            'content_id' => $event->forum->id,
            'content_type' => Forum::class
        ], [
            'title' => $event->forum->name,
            'path' => route('forum.category', [$event->forum->path], false)
        ]);
    }

    /**
     * @param ForumWasDeleted $event
     */
    public function onForumDelete(ForumWasDeleted $event)
    {
        $this->page->findByContent($event->forum['id'], Forum::class)->delete();
    }

    /**
     * @param MicroblogWasSaved $event
     */
    public function onMicroblogSave(MicroblogWasSaved $event)
    {
        $event->microblog->page()->updateOrCreate([
            'content_id'    => $event->microblog->id,
            'content_type'  => Microblog::class,
        ], [
            'title' => excerpt($event->microblog->text, 28),
            'path' => route('microblog.view', [$event->microblog->id], false)
        ]);
    }

    /**
     * @param MicroblogWasDeleted $event
     */
    public function onMicroblogDelete(MicroblogWasDeleted $event)
    {
        $this->page->findByContent($event->microblog['id'], Microblog::class)->delete();
    }

    /**
     * @param JobWasSaved $event
     */
    public function onJobSave(JobWasSaved $event)
    {
        $event->job->page()->updateOrCreate([
            'content_id'    => $event->job->id,
            'content_type'  => Job::class,
        ], [
            'title' => $event->job->title,
            'path' => route('job.offer', [$event->job->id, $event->job->path], false)
        ]);
    }

    /**
     * @param JobWasDeleted $event
     */
    public function onJobDelete(JobWasDeleted $event)
    {
        $this->page->findByContent($event->job['id'], Job::class)->delete();
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Coyote\Events\TopicWasSaved',
            'Coyote\Listeners\PageListener@onTopicSave'
        );

        $events->listen(
            'Coyote\Events\TopicWasDeleted',
            'Coyote\Listeners\PageListener@onTopicDelete'
        );

        $events->listen(
            'Coyote\Events\ForumWasSaved',
            'Coyote\Listeners\PageListener@onForumSave'
        );

        $events->listen(
            'Coyote\Events\ForumWasDeleted',
            'Coyote\Listeners\PageListener@onForumDelete'
        );

        $events->listen(
            'Coyote\Events\MicroblogWasSaved',
            'Coyote\Listeners\PageListener@onMicroblogSave'
        );

        $events->listen(
            'Coyote\Events\MicroblogWasDeleted',
            'Coyote\Listeners\PageListener@onMicroblogDelete'
        );

        $events->listen(
            'Coyote\Events\JobWasSaved',
            'Coyote\Listeners\PageListener@onJobSave'
        );

        $events->listen(
            'Coyote\Events\JobWasDeleted',
            'Coyote\Listeners\PageListener@onJobDelete'
        );
    }
}
