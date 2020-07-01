<?php

namespace Coyote\Console\Commands;

use Coyote\Events\TopicWasDeleted;
use Coyote\Forum;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as TopicRepository;
use Coyote\Topic;
use Illuminate\Console\Command;

class PurgePostsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge old posts.';

    /**
     * @var TopicRepository
     */
    private $topic;

    /**
     * @var ForumRepository
     */
    private $forum;

    public function __construct(TopicRepository $topic, ForumRepository $forum)
    {
        parent::__construct();

        $this->topic = $topic;
        $this->forum = $forum;
    }

    public function handle()
    {
        $forums = $this->forum->where('prune_days', '>', 0)->get();

        foreach ($forums as $forum) {
            $this->pruneForum($forum);
        }

        $this->info('Done.');
    }

    private function pruneForum(Forum $forum)
    {
        $topics = $this->topic->where('forum_id', $forum->id)->where('last_post_created_at', '<', now()->subDays($forum->prune_days))->get();

        foreach ($topics as $topic) {
            Topic::destroy($topic->id);

            // fire the event. it can be used to delete row from "pages" table or from search index
            event(new TopicWasDeleted($topic));

            $this->info($topic->subject . ' from ' . $forum->name . ' removed permanently.');
        }
    }
}
