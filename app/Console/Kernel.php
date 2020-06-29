<?php

namespace Coyote\Console;

use Coyote\Console\Commands\BoostJobsCommand;
use Coyote\Console\Commands\CreateCouponCommand;
use Coyote\Console\Commands\CreateSitemapCommand;
use Coyote\Console\Commands\CurrencyExchangeCommand;
use Coyote\Console\Commands\Elasticsearch\CreateIndexCommand;
use Coyote\Console\Commands\Elasticsearch\DropIndexCommand;
use Coyote\Console\Commands\Elasticsearch\IndexCommand;
use Coyote\Console\Commands\FlushCacheCommand;
use Coyote\Console\Commands\MigrateFilesystemCommand;
use Coyote\Console\Commands\PurgeFirewallCommand;
use Coyote\Console\Commands\PurgeGuestsCommand;
use Coyote\Console\Commands\PurgeJobsCommand;
use Coyote\Console\Commands\PurgePastebinCommand;
use Coyote\Console\Commands\PurgeSessionsCommand;
use Coyote\Console\Commands\PurgeViewsCommand;
use Coyote\Console\Commands\ResendApplicationCommand;
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
        PurgeViewsCommand::class,
        PurgePastebinCommand::class,
        PurgeFirewallCommand::class,
        PurgeSessionsCommand::class,
        PurgeJobsCommand::class,
        BoostJobsCommand::class,
        CreateSitemapCommand::class,
        FlushCacheCommand::class,
        CurrencyExchangeCommand::class,
        CreateIndexCommand::class,
        DropIndexCommand::class,
        IndexCommand::class,
        CreateCouponCommand::class,
        PurgeGuestsCommand::class,
        ResendApplicationCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('coyote:counter')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('job:purge')->hourly();
        $schedule->command('job:boost')->dailyAt('07:00:00');
        $schedule->command('session:purge')->everyMinute()->withoutOverlapping();
        $schedule->command('pastebin:purge')->hourly();
        $schedule->command('firewall:purge')->hourly();
        $schedule->command('sitemap:create')->dailyAt('03:00:00');
        $schedule->command('guest:purge')->dailyAt('04:00:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }
}
