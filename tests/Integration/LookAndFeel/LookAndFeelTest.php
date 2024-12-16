<?php
namespace Tests\Integration\LookAndFeel;

use Neon\Test\BaseFixture\View\ViewDom;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Integration\BaseFixture\Server\Laravel\Application;

class LookAndFeelTest extends TestCase
{
    use Application;

    #[Test]
    public function byDefault_lookAndFeel_isLegacy(): void
    {
        $response = $this->laravel->get('/');
        $this->assertTrue($this->lookAndFeelLegacy($response->content()));
    }

    private function lookAndFeelLegacy(string $htmlContent): bool
    {
        return \in_array('look-and-feel-legacy', $this->htmlBodyClasses($htmlContent));
    }

    private function htmlBodyClasses(string $htmlContent): array|false
    {
        $viewDom = new ViewDom($htmlContent);
        return \preg_split('/\s+/', \trim($viewDom->findString('//body/@class')));
    }
}
