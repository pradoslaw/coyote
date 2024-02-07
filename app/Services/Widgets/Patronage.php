<?php
namespace Coyote\Services\Widgets;

use Coyote\Microblog;
use Coyote\Repositories\Criteria\Microblog\OnlyMine;
use Coyote\Repositories\Criteria\Microblog\WithTag;
use Illuminate\Contracts\View\View;

class Patronage extends WhatsNew
{
    public function render(): string
    {
        return $this->cache->remember(
            'widget:patronage',
            now()->addHour(),
            fn() => $this->widgetView()->render());
    }

    private function widgetView(): View
    {
        $patronage = $this->patronage();
        return view('homepage.patronage', [
            'patronage' => $patronage,
            'excerpt'   => $this->excerpt($patronage),
        ]);
    }

    private function patronage(): ?Microblog
    {
        $this->microblog->resetCriteria();
        $this->microblog->pushCriteria(new WithTag('wydarzenia'));
        $this->microblog->pushCriteria(new OnlyMine($this->userIdByName('4programmers.net')));
        return $this->microblog->recent()->first();
    }

    private function excerpt(?Microblog $patronage): ?string
    {
        if ($patronage) {
            return \strip_tags($patronage->html, ['br', 'h3', 'h2', 'h4', 'strong', 'p']);
        }
        return null;
    }
}
