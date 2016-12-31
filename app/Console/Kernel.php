<?php

namespace Coyote\Console;

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
        'Coyote\Console\Commands\PurgeSessions',
        'Coyote\Console\Commands\PurgeJobs',
        'Coyote\Console\Commands\CreateSitemap',
        'Coyote\Console\Commands\Migrate',
        'Coyote\Console\Commands\Markdown',
        'Coyote\Console\Commands\FlushCache',
        'Coyote\Console\Commands\SetupTags',
        'Coyote\Console\Commands\Elasticsearch\Mapping',
        'Coyote\Console\Commands\Elasticsearch\Create',
        'Coyote\Console\Commands\Elasticsearch\Index'
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
        $schedule->command('job:purge')->hourly();
        $schedule->command('session:purge')->everyFiveMinutes();
        $schedule->command('pastebin:purge')->hourly();
        $schedule->command('firewall:purge')->hourly();
        $schedule->command('sitemap:create')->dailyAt('03:00:00');
    }
}
