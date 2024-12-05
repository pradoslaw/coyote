<?php
namespace Coyote\Feature\LookAndFeel;

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
            /** @var Request $request */
            $request = $this->app[Request::class];

            $has = $request->query->has('lookAndFeel');
            $view->with(['lookAndFeelModern' => $has]);
        });
    }
}
