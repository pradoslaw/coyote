<?php

namespace Coyote\Console;

use Coyote\Console\Commands\PlanReminderCommand;
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
        'Coyote\Console\Commands\ExpireJobs',
        'Coyote\Console\Commands\CreateSitemap',
        'Coyote\Console\Commands\FlushCache',
        'Coyote\Console\Commands\SetupTags',
        'Coyote\Console\Commands\GetCurrencyExchange',
        'Coyote\Console\Commands\Elasticsearch\Mapping',
        'Coyote\Console\Commands\Elasticsearch\Create',
        'Coyote\Console\Commands\Elasticsearch\Index',
        PlanReminderCommand::class
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
        $schedule->command('job:expire')->hourly();
        $schedule->command('job:plan-reminder')->dailyAt('07:00:00');
        $schedule->command('session:purge')->everyMinute();
        $schedule->command('pastebin:purge')->hourly();
        $schedule->command('firewall:purge')->hourly();
        $schedule->command('sitemap:create')->dailyAt('03:00:00');
        $schedule->command('currency:exchange')->dailyAt('20:00:00');
    }
}
