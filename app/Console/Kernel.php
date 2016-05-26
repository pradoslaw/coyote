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
        'Coyote\Console\Commands\PurgeViews',
        'Coyote\Console\Commands\PurgePastebin',
        'Coyote\Console\Commands\PurgeFirewall',
        'Coyote\Console\Commands\Migrate',
        'Coyote\Console\Commands\Elasticsearch\Mapping',
        'Coyote\Console\Commands\Elasticsearch\Create',
        'Coyote\Console\Commands\Elasticsearch\Index',
        'Coyote\Console\Commands\Elasticsearch\Purge',
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
        $schedule->command('es:purge')->hourly();
        $schedule->command('pastebin:purge')->hourly();
        $schedule->command('firewall:purge')->hourly();
    }
}
