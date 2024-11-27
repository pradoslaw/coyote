<?php
namespace Tests\Integration\Initials;

use Coyote\Domain\InitialsSvg;
use Neon\Test\BaseFixture\View\ViewDom;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InitialsSvgTest extends TestCase
{
    #[Test]
    public function svgImageContainsInitials(): void
    {
        $svg = new InitialsSvg('AD');
        $this->assertSame('AD', $this->svgText($svg->imageSvg()));
    }

    private function svgText(string $svgString): string
    {
        $svg = new ViewDom($svgString);
        return \trim($svg->findString('//svg/text/text()'));
    }
}
