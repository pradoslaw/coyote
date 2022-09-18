<?php

namespace Coyote\Listeners;

use Coyote\Events\GuideDeleted;
use Coyote\Events\GuideSaved;
use Coyote\Events\JobDeleted;
use Coyote\Events\MicroblogDeleted;
use Coyote\Events\MicroblogSaved;
use Coyote\Events\TopicMoved;
use Coyote\Events\TopicSaved;
use Coyote\Events\TopicDeleted;
use Coyote\Events\ForumSaved;
use Coyote\Events\ForumDeleted;
use Coyote\Events\JobWasSaved;
use Coyote\Events\WikiDeleted;
use Coyote\Events\WikiSaved;
use Coyote\Microblog;
use Coyote\Job;
use Coyote\Guide;
use Coyote\Page;
use Coyote\Services\Elasticsearch\Crawler;
use Coyote\Services\UrlBuilder;
use Coyote\Topic;
use Coyote\Forum;
use Coyote\Wiki;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Eloquent\Model;

class PageSubscriber implements ShouldQueue
{
    /**
     * Postpone this job to make sure that record was saved in transaction.
     *
     * @var int
     */
    public $delay = 10;

    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @param Kernel $kernel
     */
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param TopicSaved $event
     */
    public function onTopicSave(TopicSaved $event)
    {
        $this->updateTopic($event->topic);
    }

    /**
     * @param TopicMoved $event
     */
    public function onTopicMove(TopicMoved $event)
    {
        $this->updateTopic($event->topic);
    }

    /**
     * @param Topic $topic
     */
    private function updateTopic(Topic $topic)
    {
        $this->purgePageViews();

        $page = $topic->page()->updateOrCreate([
            'content_id'     => $topic->id,
            'content_type'   => Topic::class
        ], [
            'title'          => $topic->title,
            'tags'           => $topic->tags->pluck('name'),
            'path'           => UrlBuilder::topic($topic),
            'allow_sitemap'  => !$topic->forum->access()->exists()
        ]);

        $this->index($page);
    }

    private function index(Page | Model $page): void
    {
        dispatch(function () use ($page) {
            (new Crawler())->index($page);
        });
    }

    /**
     * @param TopicDeleted $event
     */
    public function onTopicDelete(TopicDeleted $event)
    {
        $this->deleteFromIndex($event->topic['id'], Topic::class);
    }

    /**
     * @param ForumSaved $event
     */
    public function onForumSave(ForumSaved $event)
    {
        $page = $event->forum->page()->updateOrCreate([
            'content_id'    => $event->forum->id,
            'content_type'  => Forum::class
        ], [
            'title'         => $event->forum->name,
            'path'          => UrlBuilder::forum($event->forum)
        ]);

        $this->index($page);
    }

    /**
     * @param ForumDeleted $event
     */
    public function onForumDelete(ForumDeleted $event)
    {
        $this->deleteFromIndex($event->forum['id'], Forum::class);
    }

    /**
     * @param MicroblogSaved $event
     */
    public function onMicroblogSave(MicroblogSaved $event)
    {
        if ($event->microblog->parent_id) {
            return;
        }

        $page = $event->microblog->page()->updateOrCreate([
            'content_id'    => $event->microblog->id,
            'content_type'  => Microblog::class,
        ], [
            'title'         => excerpt($event->microblog->html, 28),
            'path'          => UrlBuilder::microblog($event->microblog),
            'tags'          => $event->microblog->tags->pluck('name')
        ]);

        $this->index($page);
    }

    /**
     * @param MicroblogDeleted $event
     */
    public function onMicroblogDelete(MicroblogDeleted $event)
    {
        if ($event->microblog['parent_id']) {
            return;
        }

        $this->deleteFromIndex($event->microblog['id'], Microblog::class);
    }

    /**
     * @param JobWasSaved $event
     */
    public function onJobSave(JobWasSaved $event)
    {
        $this->purgePageViews();

        $page = $event->job->page()->updateOrCreate([
            'content_id'    => $event->job->id,
            'content_type'  => Job::class,
        ], [
            'title'         => $event->job->title,
            'tags'          => $event->job->tags->pluck('name'),
            'path'          => UrlBuilder::job($event->job)
        ]);

        $this->index($page);
    }

    /**
     * @param JobDeleted $event
     */
    public function onJobDelete(JobDeleted $event)
    {
        $this->deleteFromIndex($event->job['id'], Job::class);
    }

    /**
     * @param WikiSaved $event
     */
    public function onWikiSave(WikiSaved $event)
    {
        $this->purgePageViews();

        $page = $event->wiki->page()->updateOrCreate([
            'content_id'    => $event->wiki->id,
            'content_type'  => Wiki::class,
        ], [
            'title'         => $event->wiki->title,
            'path'          => urldecode(UrlBuilder::wiki($event->wiki))
        ]);

        $this->index($page);
    }

    /**
     * @param WikiDeleted $event
     */
    public function onWikiDelete(WikiDeleted $event)
    {
        $this->deleteFromIndex($event->wiki['id'], Wiki::class);
    }

    public function onGuideSave(GuideSaved $event)
    {
        $this->purgePageViews();

        $event->guide->page()->updateOrCreate([
            'content_id'    => $event->guide->id,
            'content_type'  => Guide::class,
        ], [
            'title'         => $event->guide->title,
            'path'          => urldecode(UrlBuilder::guide($event->guide))
        ]);
    }

    public function onGuideDelete(GuideDeleted $event)
    {
        $this->deleteFromIndex($event->guide['id'], Guide::class);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        return [
            TopicSaved::class => 'onTopicSave',
            TopicMoved::class => 'onTopicMove',
            TopicDeleted::class => 'onTopicDelete',
            ForumSaved::class => 'onForumSave',
            ForumDeleted::class => 'onForumDelete',
            MicroblogSaved::class => 'onMicroblogSave',
            MicroblogDeleted::class => 'onMicroblogDelete',
            JobWasSaved::class => 'onJobSave',
            JobDeleted::class => 'onJobDelete',
            WikiSaved::class => 'onWikiSave',
            WikiDeleted::class => 'onWikiDelete',
            GuideSaved::class => 'onGuideSave',
            GuideDeleted::class => 'onGuideDelete'
        ];
    }

    // before changing page path we MUST save page views that are stored in redis
    private function purgePageViews()
    {
        if (!app()->environment('local', 'dev')) {
            $this->kernel->call('coyote:counter');
        }
    }

    private function deleteFromIndex(int $contentId, string $contentType)
    {
        $page = Page::where('content_id', $contentId)->where('content_type', $contentType)->first();

        (new Crawler())->delete($page);

        $page->delete();
    }
}
