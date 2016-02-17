<?php namespace Coyote\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'Coyote\Console\Commands\ClearCache',
        'Coyote\Console\Commands\PurgeViews',
        'Coyote\Console\Commands\Migrate',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('coyote:counter')->everyFiveMinutes();
    }
}
