<?php

namespace Coyote\Console\Commands;

use Coyote\Microblog;
use Coyote\Page;
use Coyote\Services\UrlBuilder;
use Coyote\Topic;
use Illuminate\Console\Command;

class FixPagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:pages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix page';

    public function handle()
    {
        $this->fixTopics();
        $this->fixMicroblogs();
        $this->addMissingTopics();
    }

    private function fixTopics()
    {
        $count = Topic::count();

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        Topic::chunk(1000, function ($topics) use ($bar) {
            foreach ($topics as $topic) {
                Page::where('content_id', $topic->id)->where('content_type', Topic::class)->update([
                    'title'          => $topic->title,
                    'tags'           => $topic->tags->pluck('name'),
                    'path'           => UrlBuilder::topic($topic),
                    'allow_sitemap'  => !$topic->forum->access()->exists()
                ]);

                $bar->advance();
            }
        });

        $bar->finish();
    }

    private function fixMicroblogs()
    {
        $count = Microblog::whereNull('parent_id')->count();

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        Microblog::whereNull('parent_id')->chunkById(1000, function ($microblogs) use ($bar) {
            foreach ($microblogs as $microblog) {
                $microblog->page()->updateOrCreate([
                    'content_id'    => $microblog->id,
                    'content_type'  => Microblog::class,
                ], [
                    'title'         => excerpt($microblog->html, 28),
                    'path'          => UrlBuilder::microblog($microblog),
                    'tags'          => $microblog->tags->pluck('name')
                ]);

                $bar->advance();
            }
        });

        $bar->finish();
    }

    private function addMissingTopics()
    {
        $topics = Topic::whereRaw("topics.id not in (select content_id from pages where content_type = 'Coyote\Topic')")->get();

        $count = count($topics);

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($topics as $topic) {
            $topic->page()->updateOrCreate([
                'content_id'     => $topic->id,
                'content_type'   => Topic::class
            ], [
                'title'          => $topic->title,
                'tags'           => $topic->tags->pluck('name'),
                'path'           => UrlBuilder::topic($topic),
                'allow_sitemap'  => !$topic->forum->access()->exists()
            ]);

            $bar->advance();
        }

        $bar->finish();
    }
}
