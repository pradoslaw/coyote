<?php
namespace Neon\Test\Unit\View\Fixture;

use Neon\Test\BaseFixture\WebClient;
use Tests\Integration\BaseFixture;

trait CssFixture
{
    use BaseFixture\Server\Laravel\Application;

    private function computedStyle(string $url, string $querySelector): mixed
    {
        $client = new WebClient();
        $client->navigateTo('http://nginx' . $url);
        $result = $client->computedStyle($querySelector);
        $client->close();
        return $result;
    }
}
