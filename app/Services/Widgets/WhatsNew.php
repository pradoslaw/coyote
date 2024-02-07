<?php
namespace Coyote\Services\Widgets;

use Coyote\Microblog;
use Coyote\Repositories\Criteria\Microblog\OnlyMine;
use Coyote\Repositories\Criteria\Microblog\WithTag;
use Coyote\Repositories\Eloquent\MicroblogRepository;
use Coyote\User;
use Illuminate\Contracts\Cache;
use Illuminate\Contracts\View\View;
use Illuminate\Support;

class WhatsNew
{
    public function __construct(
        protected MicroblogRepository $microblog,
        protected Cache\Repository    $cache)
    {
    }

    public function render(): string
    {
        return $this->cache->remember(
            'widget:whats-new',
            now()->addHour(),
            fn() => $this->widgetView()->render());
    }

    private function widgetView(): View
    {
        return view('homepage.whats-new', [
            'href'       => route('microblog.tag', ['4programmers.net']),
            'microblogs' => $this->microblogs(fn(Microblog $microblog) => [
                'id'      => $microblog->id,
                'summary' => excerpt($microblog->html),
                'href'    => route('microblog.view', [$microblog->id]),
                'date'    => $microblog->created_at->formatLocalized('%d %b %y'),
            ]),
        ]);
    }

    private function microblogs(callable $callback): Support\Collection
    {
        $this->microblog->resetCriteria();
        $this->microblog->pushCriteria(new WithTag('4programmers.net'));
        $this->microblog->pushCriteria(new OnlyMine($this->userIdByName('4programmers.net')));
        return $this->microblog->recent()->map($callback);
    }

    protected function userIdByName(string $name): ?int
    {
        $user = User::query()->where('name', '=', $name)->first(['id']);
        return $user->id ?? null;
    }
}
