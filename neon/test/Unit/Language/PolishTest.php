<?php
namespace Neon\Test\Unit\Language;

use Neon\View\Language\Polish;
use PHPUnit\Framework\TestCase;
use function Neon\Test\BaseFixture\Caught\caught;

class PolishTest extends TestCase
{
    /**
     * @test
     */
    public function free(): void
    {
        $language = new Polish();
        $this->assertSame('Bezpłatne', $language->t('Free'));
    }

    /**
     * @test
     */
    public function paid(): void
    {
        $language = new Polish();
        $this->assertSame('Płatne', $language->t('Paid'));
    }

    /**
     * @test
     */
    public function missing(): void
    {
        $language = new Polish();
        $exception = caught(fn() => $language->t('foo'));
        $this->assertSame(
            "Failed to translate phrase: 'foo'.",
            $exception->getMessage());
    }

    /**
     * @test
     */
    public function declinationSingularNominative(): void
    {
        $language = new Polish();
        $this->assertSame('miasto', $language->dec(1, 'cities'));
    }

    /**
     * @test
     */
    public function declinationPluralNominative(): void
    {
        $language = new Polish();
        $this->assertSame('miasta', $language->dec(2, 'cities'));
    }

    /**
     * @test
     */
    public function declinationPluralGenitive(): void
    {
        $language = new Polish();
        $this->assertSame('miast', $language->dec(5, 'cities'));
    }
}
