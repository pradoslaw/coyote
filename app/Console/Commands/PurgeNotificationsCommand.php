<?php

namespace Coyote\Console\Commands;

use Coyote\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Console\Command;

class PurgeNotificationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge old notifications.';

    public function handle(NotificationRepositoryInterface $notification)
    {
        $notification->purge();

        $this->info('Done.');
    }
}
