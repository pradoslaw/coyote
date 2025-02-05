<?php
namespace Coyote\Feature\DraftPost;

use Coyote\Services\Guest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Factory;

class DraftPostServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var Factory $view */
        $view = $this->app['view'];
        $this->add($view);

        Route::middleware('web')->group(function () {
            Route::post('/Forum/Draft', function (Request $request, DraftPostService $post, Guest $guest) {
                $guest->createIfMissing();
                $post->insert($request->get('markdownText'), $request->get('topicId'), $guest->id);
            });
        });
    }

    private function add(Factory $viewFactory): void
    {
        $viewFactory->composer('forum.topic', function (View $view) {
            $draftPost = app(DraftPostService::class);
            $guest = app(Guest::class);
            [$topicId, $text] = $draftPost->fetchDraft($guest->id);
            $view->with([
                'draftPost' => [
                    'text'    => $text,
                    'topicId' => $topicId,
                ],
            ]);
        });
    }
}
