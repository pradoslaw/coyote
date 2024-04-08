<?php
namespace Neon\Test\Unit\Language;

use Neon\View\Language\English;
use PHPUnit\Framework\TestCase;

class EnglishTest extends TestCase
{
    /**
     * @test
     */
    public function free(): void
    {
        $language = new English();
        $this->assertSame('cities', $language->dec(2, 'cities'));
    }
}
