<?php

namespace Coyote\Listeners;

use Coyote\Events\JobDeleted;
use Coyote\Events\MicroblogDeleted;
use Coyote\Events\MicroblogSaved;
use Coyote\Events\TopicWasMoved;
use Coyote\Events\TopicWasSaved;
use Coyote\Events\TopicWasDeleted;
use Coyote\Events\ForumWasSaved;
use Coyote\Events\ForumWasDeleted;
use Coyote\Events\JobWasSaved;
use Coyote\Events\WikiWasDeleted;
use Coyote\Events\WikiWasSaved;
use Coyote\Microblog;
use Coyote\Job;
use Coyote\Services\UrlBuilder;
use Coyote\Topic;
use Coyote\Forum;
use Coyote\Repositories\Contracts\PageRepositoryInterface as PageRepository;
use Coyote\Wiki;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Console\Kernel;

class PageSubscriber implements ShouldQueue
{
    /**
     * Postpone this job to make sure that record was saved in transaction.
     *
     * @var int
     */
    public $delay = 10;

    /**
     * @var PageRepository
     */
    protected $page;

    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @param PageRepository $page
     * @param Kernel $kernel
     */
    public function __construct(PageRepository $page, Kernel $kernel)
    {
        $this->page = $page;
        $this->kernel = $kernel;
    }

    /**
     * @param TopicWasSaved $event
     */
    public function onTopicSave(TopicWasSaved $event)
    {
        $this->updateTopic($event->topic);
    }

    /**
     * @param TopicWasMoved $event
     */
    public function onTopicMove(TopicWasMoved $event)
    {
        $this->updateTopic($event->topic);
    }

    /**
     * @param Topic $topic
     */
    private function updateTopic(Topic $topic)
    {
        $this->purgePageViews();

        $topic->page()->updateOrCreate([
            'content_id'     => $topic->id,
            'content_type'   => Topic::class
        ], [
            'title'          => $topic->title,
            'tags'           => $topic->tags->pluck('name'),
            'path'           => UrlBuilder::topic($topic),
            'allow_sitemap'  => !$topic->forum->access()->exists()
        ]);
    }

    /**
     * @param TopicWasDeleted $event
     */
    public function onTopicDelete(TopicWasDeleted $event)
    {
        $this->page->deleteByContent($event->topic['id'], Topic::class);
    }

    /**
     * @param ForumWasSaved $event
     */
    public function onForumSave(ForumWasSaved $event)
    {
        $event->forum->page()->updateOrCreate([
            'content_id'    => $event->forum->id,
            'content_type'  => Forum::class
        ], [
            'title'         => $event->forum->name,
            'path'          => UrlBuilder::forum($event->forum)
        ]);
    }

    /**
     * @param ForumWasDeleted $event
     */
    public function onForumDelete(ForumWasDeleted $event)
    {
        $this->page->deleteByContent($event->forum['id'], Forum::class);
    }

    /**
     * @param MicroblogSaved $event
     */
    public function onMicroblogSave(MicroblogSaved $event)
    {
        $event->microblog->page()->updateOrCreate([
            'content_id'    => $event->microblog->id,
            'content_type'  => Microblog::class,
        ], [
            'title'         => excerpt($event->microblog->html, 28),
            'path'          => UrlBuilder::microblog($event->microblog),
            'tags'          => $event->microblog->tags->pluck('name')
        ]);
    }

    /**
     * @param MicroblogDeleted $event
     */
    public function onMicroblogDelete(MicroblogDeleted $event)
    {
        $this->page->deleteByContent($event->microblog['id'], Microblog::class);
    }

    /**
     * @param JobWasSaved $event
     */
    public function onJobSave(JobWasSaved $event)
    {
        $this->purgePageViews();

        $event->job->page()->updateOrCreate([
            'content_id'    => $event->job->id,
            'content_type'  => Job::class,
        ], [
            'title'         => $event->job->title,
            'tags'          => $event->job->tags->pluck('name'),
            'path'          => UrlBuilder::job($event->job)
        ]);
    }

    /**
     * @param JobDeleted $event
     */
    public function onJobDelete(JobDeleted $event)
    {
        $this->page->deleteByContent($event->job['id'], Job::class);
    }

    /**
     * @param WikiWasSaved $event
     */
    public function onWikiSave(WikiWasSaved $event)
    {
        $this->purgePageViews();

        $event->wiki->page()->updateOrCreate([
            'content_id'    => $event->wiki->id,
            'content_type'  => Wiki::class,
        ], [
            'title'         => $event->wiki->title,
            'path'          => urldecode(UrlBuilder::wiki($event->wiki))
        ]);
    }

    /**
     * @param WikiWasDeleted $event
     */
    public function onWikiDelete(WikiWasDeleted $event)
    {
        $this->page->deleteByContent($event->wiki['id'], Wiki::class);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        return [
            TopicWasSaved::class => 'onTopicSave',
            TopicWasMoved::class => 'onTopicMove',
            TopicWasDeleted::class => 'onTopicDelete',
            ForumWasSaved::class => 'onForumSave',
            ForumWasDeleted::class => 'onForumDelete',
            MicroblogSaved::class => 'onMicroblogSave',
            MicroblogDeleted::class => 'onMicroblogDelete',
            JobWasSaved::class => 'onJobSave',
            JobDeleted::class => 'onJobDelete',
            WikiWasSaved::class => 'onWikiSave',
            WikiWasDeleted::class => 'onWikiDelete'
        ];
    }

    // before changing page path we MUST save page views that are stored in redis
    private function purgePageViews()
    {
        if (!app()->environment('local', 'dev')) {
            $this->kernel->call('coyote:counter');
        }
    }
}
