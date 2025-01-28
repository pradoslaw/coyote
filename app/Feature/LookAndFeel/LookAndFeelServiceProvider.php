<?php
namespace Coyote\Feature\LookAndFeel;

use Coyote\Services\Guest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Factory;

class LookAndFeelServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var Factory $view */
        $view = $this->app['view'];
        $view->composer('layout', function (View $view) {
            $view->with([
                'accessToLookAndFeel' => auth()->check(),
                'lookAndFeelModern'   => $this->lookAndFeel() === 'modern',
            ]);
        });
        Route::middleware(['web', 'auth'])->group(function () {
            Route::post('/LookAndFeel', function (Request $request) {
                $this->setLookAndFeel($request->get('lookAndFeel') === 'modern');
            });
        });
    }

    private function lookAndFeel(): string
    {
        if (!auth()->check()) {
            return 'modern';
        }
        $guest = new Guest(auth()->user()->guest_id);
        if ($guest->getSetting('lookAndFeel') === 'modern') {
            return 'modern';
        }
        if ($guest->getSetting('lookAndFeel') === 'legacy') {
            return 'legacy';
        }
        return 'modern';
    }

    private function setLookAndFeel(bool $isModern): void
    {
        if (!auth()->check()) {
            return;
        }
        $guest = new Guest(auth()->user()->guest_id);
        $guest->setSetting('lookAndFeel', $isModern ? 'modern' : 'legacy');
    }
}
