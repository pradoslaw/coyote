<?php

namespace Coyote\Services\Widgets;

use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as MicroblogRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Repositories\Criteria\Microblog\OnlyMine;
use Coyote\Repositories\Criteria\Microblog\WithTag;
use Illuminate\Contracts\Cache\Repository as Cache;

class WhatsNew
{
    private MicroblogRepository $microblog;
    private UserRepository $user;
    private Cache $cache;

    public function __construct(MicroblogRepository $microblog, UserRepository $user, Cache $cache)
    {
        $this->microblog = $microblog;
        $this->user = $user;
        $this->cache = $cache;
    }

    public function render(): string
    {
        return $this->cache->remember('widget:whats-new', now()->addHour(), function () {
            $this->user->resetCriteria();

            $user = $this->user->findBy('name', '4programmers.net', ['id']);

            $this->microblog->resetCriteria();
            $this->microblog->pushCriteria(new WithTag('4programmers.net'));
            $this->microblog->pushCriteria(new OnlyMine($user->id));

            return view('homepage.whats-new', ['microblogs' => $this->microblog->recent()])->render();
        });
    }
}
