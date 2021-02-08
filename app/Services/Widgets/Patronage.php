<?php

namespace Coyote\Services\Widgets;

use Coyote\Repositories\Criteria\Microblog\OnlyMine;
use Coyote\Repositories\Criteria\Microblog\WithTag;

class Patronage extends WhatsNew
{
    public function render(): string
    {
        return $this->cache->remember('widget:patronage', now()->addHour(), function () {
            $this->user->resetCriteria();

            $user = $this->user->findBy('name', '4programmers.net', ['id']);

            $this->microblog->resetCriteria();
            $this->microblog->pushCriteria(new WithTag('wydarzenia'));
            $this->microblog->pushCriteria(new OnlyMine($user->id ?? null));

            $patronage = $this->microblog->recent()->first();
            $excerpt = null;

            if ($patronage) {
                $excerpt = strip_tags($patronage->html, ['br', 'h3', 'h2', 'h4', 'strong', 'p']);
            }

            return view('homepage.patronage', ['patronage' => $patronage, 'excerpt' => $excerpt])->render();
        });
    }
}
