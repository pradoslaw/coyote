<?php
namespace Tests\Unit\Breadcrumbs\Fixture;

use Illuminate\Config;
use Tests\Unit\BaseFixture\Server\Laravel;

trait SystemApplication
{
    use Laravel\Application;

    function systemApplicationName(string $applicationName): void
    {
        /** @var Config\Repository $config */
        $config = $this->laravel->app['config'];
        $config->set('app.name', $applicationName);
    }
}
