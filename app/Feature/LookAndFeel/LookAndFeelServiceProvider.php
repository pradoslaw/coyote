<?php
namespace Coyote\Feature\LookAndFeel;

use Coyote\Services\Guest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Factory;

class LookAndFeelServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var Factory $view */
        $view = $this->app['view'];

        $view->composer('layout', function (View $view) {
            $view->with(['lookAndFeelModern' => $this->lookAndFeel() === 'modern']);
        });
    }

    private function lookAndFeel(): string
    {
        return $this->requestOverride() ?? $this->userSetting() ?? 'legacy';
    }

    private function userSetting(): ?string
    {
        if (!auth()->check()) {
            return null;
        }
        $guest = new Guest(auth()->user()->guest_id);
        if ($guest->getSetting('lookAndFeel') === 'modern') {
            return 'modern';
        }
        if ($guest->getSetting('lookAndFeel') === 'legacy') {
            return 'legacy';
        }
        return null;
    }

    private function requestOverride(): ?string
    {
        /** @var Request $request */
        $request = $this->app[Request::class];
        if ($request->query->get('lookAndFeel') === 'legacy') {
            return 'legacy';
        }
        if ($request->query->has('lookAndFeel')) {
            return 'modern';
        }
        return null;
    }
}
