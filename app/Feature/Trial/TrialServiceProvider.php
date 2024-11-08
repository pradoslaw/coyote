<?php
namespace Coyote\Feature\Trial;

use Coyote\Domain\Settings\UserTheme;
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
        });
    }

    private function add(Factory $viewFactory): void
    {
        $viewFactory->composer('home', $this->addViewField(...));
    }

    private function addViewField(View $view): void
    {
        $view->with([
            'survey' => [
                'trial'         => [
                    'title'       => 'Wygląd strony głównej.',
                    'reason'      => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras velit metus, egestas id facilisis vel, consectetur sit amet magna. Praesent auctor arcu augue, ut efficitur dui rhoncus et.',
                    'solution'    => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras velit metus, egestas id facilisis vel, consectetur sit amet magna. Praesent auctor arcu augue, ut efficitur dui rhoncus et. Donec tempus dapibus justo a faucibus.',
                    'dueDateTime' => '2024-11-15 16:00:00',
                    'imageLight'  => [
                        'imageLegacy' => '/img/survey/homepage/legacy.light.png',
                        'imageModern' => '/img/survey/homepage/modern.light.png',
                    ],
                    'imageDark'   => [
                        'imageLegacy' => '/img/survey/homepage/legacy.dark.png',
                        'imageModern' => '/img/survey/homepage/modern.dark.png',
                    ],
                ],
                'userSession'   => [
                    'stage'      => 'stage-none',
                    'choice'     => 'choice-modern', 'choice-legacy', 'choice-pending',
                    'badgeLong'  => true,
                    'assortment' => 'assortment-legacy', // 'assortment-modern'
                ],
                'userThemeDark' => $this->userTheme()->isThemeDark(),
            ],
        ]);
    }

    private function userTheme(): UserTheme
    {
        /** @var UserTheme $theme */
        $theme = $this->app[UserTheme::class];
        return $theme;
    }
}
