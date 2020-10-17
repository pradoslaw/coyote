<?php

namespace Coyote\Console\Commands;

use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;
use Illuminate\Console\Command;

class CalculateTagWeight extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tags:calculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate tags counter';

    /**
     * @var TagRepository
     */
    private $tag;

    /**
     * @param TagRepository $tag
     */
    public function __construct(TagRepository $tag)
    {
        parent::__construct();

        $this->tag = $tag;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->tag->countTopics();

        $this->info('Done.');
    }
}
