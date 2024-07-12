<?php
namespace Tests\Unit\Markdown;

use Coyote\Services\Parser\Factories\PostFactory;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;
use Tests\Unit\BaseFixture\Server\Laravel;
use Tests\Unit\Seo;

class CssClassInjectTest extends TestCase
{
    use Laravel\Application;
    use BaseFixture\ClearedCache;

    #[Test]
    public function divClassCssInject(): void
    {
        $this->assertRenderPost('<div class="inject">', "<div>\n</div>");
    }

    private function assertRenderPost(string $text, string $expected): void
    {
        $parser = new PostFactory($this->laravel->app);
        Assert::assertSame($expected, $parser->parse($text));
    }
}
