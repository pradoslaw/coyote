<?php
namespace Coyote\Providers\Neon;

use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Neon\Application;
use Neon\Laravel\AppVisitor;
use Neon\Persistence;

class ServiceProvider extends RouteServiceProvider
{
    public function register(): void
    {
        parent::register();
        $this->app->instance(
            Application::class,
            new Application('4programmers.net',
                $this->attendance(),
                new AppVisitor($this->app)));
    }

    public function loadRoutes(): void
    {
        $this->get('/events', [
            'uses' => function () {
                /** @var Application $application */
                $application = $this->app->get(Application::class);
                return $application->html();
            },
        ])->middleware('neon');
    }

    private function attendance(): Persistence\Attendance
    {
        /** @var DatabaseManager $database */
        $database = $this->app->get(DatabaseManager::class);
        return new \Neon\Laravel\DatabaseAttendance($database);
    }
}
