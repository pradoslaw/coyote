<?php

namespace Coyote\Console\Commands;

use Coyote\Repositories\Contracts\PageRepositoryInterface as PageRepository;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;

class PurgeViewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coyote:counter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Increment pages views';

    /**
     * @var PageRepository
     */
    private $page;

    /**
     * @var mixed
     */
    private $redis;

    /**
     * Create a new command instance.
     *
     * @param PageRepository $page
     */
    public function __construct(PageRepository $page)
    {
        parent::__construct();

        $this->page = $page;
        $this->redis = app('redis');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        // get hits as serialized arrays
        $keys = $this->redis->smembers('hits');

        if (!$keys) {
            return;
        }

        // hits as groupped collection
        $pages = collect(array_map('unserialize', $keys))->groupBy('path');

        app(Connection::class)->transaction(function () use ($pages, $keys) {
            foreach ($pages as $path => $hits) {
                /** @var \Coyote\Page $page */
                $page = $this->page->findByPath('/' . $path);

                if (!empty($page->id)) {
                    $content = $page->content()->getResults();

                    if ($content) {
                        $content->timestamps = false;
                        $content->increment('views', count($hits));

                        $this->store($page, $hits);

                        $this->info('Added ' . count($hits) . ' views to: ' . $path);
                    }
                }
            }

            $this->redis->srem('hits', $keys);
        });
    }

    /**
     * @param \Coyote\Page $page
     * @param array[] $hits
     */
    private function store($page, $hits)
    {
        foreach ($hits as $hit) {
            if ($hit['user_id']) {
                /** @var \Coyote\Page\Visit $visits */
                $visits = $page->visits()->firstOrNew(['user_id' => $hit['user_id']]);
                $visits->visits++;

                $visits->save();
            }
        }
    }
}
