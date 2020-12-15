<?php

namespace Coyote\Services\Widgets;

use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as MicroblogRepository;
use Coyote\Repositories\Criteria\Microblog\WithTag;
use Illuminate\Contracts\Cache\Repository as Cache;

class WhatsNew
{
    private MicroblogRepository $microblog;
    private Cache $cache;

    public function __construct(MicroblogRepository $microblog, Cache $cache)
    {
        $this->microblog = $microblog;
        $this->cache = $cache;
    }

    public function render(): string
    {
        return $this->cache->remember('widget:whats-new', now()->addHour(), function () {
            $this->microblog->resetCriteria();
            $this->microblog->pushCriteria(new WithTag('4programmers.net'));

            return view('homepage.whats-new', ['microblogs' => $this->microblog->recent()])->render();
        });
    }
}
