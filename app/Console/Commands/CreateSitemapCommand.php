<?php

namespace Coyote\Console\Commands;

use Illuminate\Console\Command;
use Coyote\Repositories\Contracts\PageRepositoryInterface as PageRepository;

class CreateSitemapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create sitemap';

    /**
     * @var PageRepository
     */
    protected $page;

    /**
     * @param PageRepository $page
     */
    public function __construct(PageRepository $page)
    {
        parent::__construct();

        $this->page = $page;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '1GB');

        $sitemap = $this->getSitemap();
        $bar = $this->output->createProgressBar($this->page->forSitemap()->count());

        $this->page->forSitemap()->chunk(10000, function ($pages) use ($sitemap, $bar) {
            /** @var \Coyote\Page $page */
            foreach ($pages as $page) {
                $sitemap
                    ->add(url($page->path, [], true), $page->updated_at->toIso8601String());

                $bar->advance();
            }
        });

        $bar->finish();

        $this->info("\nSaving. Please wait...");
        $sitemap->save();

        $this->info("Done.");
    }

    /**
     * @return \Coyote\Services\Sitemap\Sitemap
     */
    private function getSitemap()
    {
        return app('sitemap');
    }
}
