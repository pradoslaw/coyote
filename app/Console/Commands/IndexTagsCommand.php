<?php

namespace Coyote\Console\Commands;

use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Services\Elasticsearch\Crawler;
use Coyote\Tag;
use Illuminate\Console\Command;

class IndexTagsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tags:index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindex recently updated tags.';

    /**
     * @var ForumRepository
     */
    private ForumRepository $forum;

    /**
     * @param ForumRepository $forum
     */
    public function __construct(ForumRepository $forum)
    {
        parent::__construct();

        $this->forum = $forum;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->updateIndex();
        $this->popularTags();

        $this->info('Done.');

        return 0;
    }

    private function popularTags()
    {
        $forums = $this->forum->all();

        /** @var \Coyote\Forum $forum */
        foreach ($forums as $forum) {
            $keys = array_pluck($this->forum->popularTags($forum->id), 'id');

            if (!$keys) {
                continue;
            }

            $values = [];

            for ($i = 1; $i <= count($keys); $i++) {
                $values[] = ['order' => $i];
            }

            $forum->tags()->sync(array_combine($keys, $values));
        }
    }

    private function updateIndex()
    {
        $then = now()->subMinutes(6);
        $tags = Tag::withTrashed()->where('updated_at', '>', $then)->orWhere('last_used_at', '>', $then)->get();

        $crawler = new Crawler();

        foreach ($tags as $tag) {
            $this->info("Indexing $tag->name ...");

            $tag->deleted_at ? $crawler->delete($tag) : $crawler->index($tag);
        }
    }
}
