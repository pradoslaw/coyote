<?php
namespace Neon\Test\Unit\View;

use PHPUnit\Framework\TestCase;

class StyleTest extends TestCase
{
    use Fixture\CssFixture;

    /**
     * @test
     * @large
     */
    public function headerFlex()
    {
        $styles = $this->computedStyle('/events', 'header');
        $this->assertSame('flex', $styles['display']);
    }
}
