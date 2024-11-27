<?php
namespace Tests\Integration\Markdown;

use Coyote\Services\Parser\Factories\PostFactory;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Integration\BaseFixture;
use Tests\Integration\BaseFixture\Server\Laravel;
use Tests\Integration\Seo;

class CssClassInjectTest extends TestCase
{
    use Laravel\Application;
    use BaseFixture\ClearedCache;

    #[Test]
    public function divClassCssInject(): void
    {
        $this->assertRenderPost('<div class="inject">', "<div>\n</div>");
    }

    #[Test]
    public function spanStyleInject(): void
    {
        $this->assertRenderPost('<span style="font-size:1.0em;">', "<span>\n</span>");
    }

    private function assertRenderPost(string $text, string $expected): void
    {
        $parser = new PostFactory($this->laravel->app);
        Assert::assertSame($expected, $parser->parse($text));
    }
}
