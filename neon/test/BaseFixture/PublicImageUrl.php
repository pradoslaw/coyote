<?php
namespace Neon\Test\BaseFixture;

use Illuminate\Config\Repository;
use Tests\Integration\BaseFixture\Server\Laravel;

trait PublicImageUrl
{
    use Laravel\Application;

    function publicImageBaseUrl(string $value): void
    {
        /** @var Repository $config */
        $config = $this->laravel->app->get(Repository::class);
        $config->set('filesystems.disks.public.url', $value);
    }
}
