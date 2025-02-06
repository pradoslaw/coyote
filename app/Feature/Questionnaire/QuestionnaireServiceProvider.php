<?php
namespace Coyote\Feature\Questionnaire;

use Carbon\Carbon;
use Coyote\Services\Guest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Factory;

class QuestionnaireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var Factory $view */
        $view = $this->app['view'];
        $this->add($view);

        Route::middleware('web')->group(function () {
            Route::post('/Questionnaire', function (Request $request, Guest $guest) {
                $answer = $request->get('questionnaireAnswer');
                $guest->setSetting('questionnaireAnswer', $answer);
                return response([]);
            });
            Route::post('/Questionnaire/See', function (Request $request, Guest $guest) {
                $guest->setSetting('questionnaireSee', Carbon::now()->toIso8601String());
                return response([]);
            });
        });
    }

    private function add(Factory $viewFactory): void
    {
        $viewFactory->composer('layout', function (View $view) {
            /** @var Guest $guest */
            $guest = app(Guest::class);
            if ($guest->getSetting('questionnaireAnswer') !== null) {
                return;
            }
            $view->with([
                'questionnaire' => [
                    'question' => 'Jak oceniasz obecną sytuację na rynku pracy dla programistów?',
                    'subtitle' => 'Prosimy o ocenę w skali od 1 do 5, gdzie 1 oznacza "bardzo niekorzystną", a 5 "bardzo korzystną"',
                    'answers'  => ['1', '2', '3', '4', '5'],
                ],
            ]);
        });
    }
}
