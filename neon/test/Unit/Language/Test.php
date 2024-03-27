<?php
namespace Neon\Test\Unit\Language;

use Neon\View\Language;
use PHPUnit\Framework\TestCase;
use function Neon\Test\BaseFixture\Caught\caught;

class Test extends TestCase
{
    /**
     * @test
     */
    public function free(): void
    {
        $language = new Language();
        $this->assertSame('Bezpłatne', $language->t('Free'));
    }

    /**
     * @test
     */
    public function paid(): void
    {
        $language = new Language();
        $this->assertSame('Płatne', $language->t('Paid'));
    }

    /**
     * @test
     */
    public function missing(): void
    {
        $language = new Language();
        $exception = caught(fn() => $language->t('foo'));
        $this->assertSame(
            "Failed to translate phrase: 'foo'.",
            $exception->getMessage());
    }
}
