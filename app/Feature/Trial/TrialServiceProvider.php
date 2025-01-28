<?php
namespace Coyote\Feature\Trial;

use Coyote\Services\Session\Renderer;
use Coyote\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Factory;

class TrialServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var Factory $view */
        $view = $this->app['view'];
        $this->add($view);

        Route::middleware('web')->group(function () {
            Route::post('/trial/choice', function (Request $request, TrialService $service) {
                $service->setChoice($request->get('choice'));
            });
            Route::post('/trial/stage', function (Request $request, TrialService $service) {
                $service->setStage($request->get('stage'));
            });
            Route::post('/trial/preview', function (Request $request, TrialService $service) {
                $service->logPreview($request->get('preview'));
            });
            Route::post('/trial/badge', function (Request $request, TrialService $service) {
                $service->setBadgeNarrow($request->get('badge') === 'narrow');
            });
            Route::post('/trial/enroll', function (TrialService $service) {
                $service->enrolled();
            });
        });
    }

    private function add(Factory $viewFactory): void
    {
        $viewFactory->composer('home', function (View $view) {
            $view->with([
                'isHomepageModern' => false,
                'homepageMembers'  => $this->members(),
            ]);
        });
    }

    private function members(): array
    {
        /** @var Renderer $renderer */
        $renderer = app(Renderer::class);
        $viewers = $renderer->sessionViewers('/');
        return [
            'usersTotal'   => \number_format(User::query()->withTrashed()->count(), thousands_separator:'.'),
            'usersOnline'  => \count($viewers->users) + $viewers->guestsCount,
            'guestsOnline' => $viewers->guestsCount,
        ];
    }
}
