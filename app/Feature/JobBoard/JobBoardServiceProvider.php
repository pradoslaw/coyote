<?php
namespace Coyote\Feature\JobBoard;

use Carbon\Carbon;
use Coyote\Services\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class JobBoardServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware('web')->group(function () {
            Route::post('/JobBoard/Milestone', function (Request $request, Guest $guest) {
                if ($request->has('milestone')) {
                    $this->appendEvent($guest, 
                        $request->get('milestone'),
                        Carbon::now()->toIso8601String());
                }
                return response([]);
            });
        });
    }

    private function appendEvent(Guest $guest, string $answer, string $key): void
    {
        $events = $guest->getSetting('jobBoard', []);
        $guest->setSetting('jobBoard', [...$events, $key => $answer]);
    }
}
