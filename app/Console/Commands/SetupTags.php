<?php

namespace Coyote\Console\Commands;

use Coyote\Repositories\Contracts\PageRepositoryInterface as PageRepository;
use Illuminate\Console\Command;

class SetupTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coyote:setup-tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup tags in pages table.';

    /**
     * @var PageRepository
     */
    private $page;

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
     *
     * @return mixed
     */
    public function handle()
    {
        $result = $this->page->whereIn('content_type', ['Coyote\Topic', 'Coyote\Microblog', 'Coyote\Job'])->get();
        $bar = $this->output->createProgressBar(count($result));

        foreach ($result as $row) {
            $content = $row->content()->getResults();

            if ($content) {
                $row->tags = $content->tags->pluck('name');
                $row->save();
            }

            $bar->advance();
        }

        $bar->finish();
    }
}
