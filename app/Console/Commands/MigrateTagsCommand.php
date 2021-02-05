<?php

namespace Coyote\Console\Commands;

use Coyote\Repositories\Contracts\TagRepositoryInterface;
use Coyote\Services\Elasticsearch\Crawler;
use Coyote\Tag;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;

class MigrateTagsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tags:migrate {--from=} {--to=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge two tags.';

    /**
     * @param Connection $db
     * @param TagRepositoryInterface $repository
     * @return int
     * @throws \Throwable
     */
    public function handle(Connection $db, TagRepositoryInterface $repository)
    {
        $from = Tag::where('name', $this->option('from'))->firstOrFail();
        $to = Tag::where('name', $this->option('to'))->firstOrFail();

        $db->transaction(function () use ($from, $to, $repository) {
            $repository->merge($from, $to);

            $from->forceDelete();

            $crawler = new Crawler();
            $crawler->delete($from);
            $crawler->index($to);
        });

        $this->info('Done.');

        return 0;
    }
}
