<?php
namespace Coyote\Feature\Trial;

use Coyote\Domain\Settings\UserTheme;
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
        $viewFactory->composer('home_modern', function (View $view) {
            $view->with([
                'isHomepageModern' => true,
                'homepageMembers'  => $this->members(),
            ]);
        });
//        $viewFactory->composer(['home', 'home_modern'], $this->addViewField(...));
    }

    private function addViewField(View $view): void
    {
        /** @var TrialService $service */
        $service = app(TrialService::class);
        $view->with([
            'survey' => [
                'trial'         => [
                    'title'       => 'Wygląd strony głównej.',
                    'reason'      => 'Redesign strony głównej forum programistycznego został przeprowadzony w celu podniesienia estetyki i spójności wizualnej. Pomimo braku istotnych zmian w layoutcie, skupiono się na uwydatnieniu i poprawieniu kluczowych elementów, aby strona stała się bardziej przyjazna, przejrzysta i nowoczesna.',
                    'solution'    => 'W pierwszej kolejności, zadbano o dostosowanie kolorystyki strony do koncepcji brandingu. Dodatkowo wyrównano paddingi i marginesy, a także zwiększono odstępy między poszczególnymi elementami, co dodało stronie przestrzeni i poprawiło jej przejrzystość.',
                    'dueDateTime' => '2024-11-19 23:59:59',
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
                    'stage'      => $service->getUserStage(),
                    'choice'     => $service->getUserChoice(),
                    'badgeLong'  => $service->isUserBadgeLong(),
                    'assortment' => $service->getUserAssortment(),
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
