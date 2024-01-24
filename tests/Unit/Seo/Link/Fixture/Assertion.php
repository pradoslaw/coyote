<?php
namespace Tests\Unit\Seo\Link\Fixture;

use Coyote\Services\Parser\Factories\PostFactory;
use Illuminate\Contracts\Cache;
use PHPUnit\Framework\Assert;
use Tests\Unit\BaseFixture\Server\Laravel;

trait Assertion
{
    use Laravel\Application;

    /**
     * @before
     */
    function clearCache(): void
    {
        /** @var Cache\Repository $cache */
        $cache = $this->laravel->app[Cache\Repository::class];
        $cache->clear();
    }

    function assertRenderPost(string $text, string $expected): void
    {
        $parser = new PostFactory($this->laravel->app);
        Assert::assertSame("$expected\n", $parser->parse($text));
    }
}
