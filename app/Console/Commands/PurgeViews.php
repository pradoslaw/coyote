<?php

namespace Coyote\Console\Commands;

use Coyote\Repositories\Contracts\PageRepositoryInterface as PageRepository;
use Illuminate\Console\Command;

class PurgeViews extends Command
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
     * Create a new command instance.
     *
     * @param PageRepository $page
     */
    public function __construct(PageRepository $page)
    {
        parent::__construct();

        $this->page = $page;
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $redis = app('redis');
        $keys = $redis->keys('hit:*');

        foreach ($keys as $key) {
            list(, $path) = explode(':', $key);

            /** @var \Coyote\Page $page */
            $page = $this->page->findByPath('/' . trim($path, '/'));
            if (!empty($page->id)) {
                $content = $page->content();

                if ($content) {
                    $hits = $redis->smembers($key);

                    $content = $content->getResults();
                    $content->timestamps = false;
                    $content->increment('views', count($hits));

                    $this->store($page, $hits);

                    $this->info('Added ' . count($hits) . ' views to: ' . $path);
                }
            }

            $redis->del($key); // remove key from redis no matter what
        }
    }

    /**
     * @param \Coyote\Page $page
     * @param array[] $hits
     */
    private function store($page, $hits)
    {
        foreach ($hits as $hit) {
            list($userId, ) = explode(';', $hit);

            if (is_numeric($userId)) {
                /** @var \Coyote\Page\Visit $visits */
                $visits = $page->visits()->firstOrNew(['user_id' => $userId]);
                $visits->visits++;

                $visits->save();
            }
        }
    }
}
