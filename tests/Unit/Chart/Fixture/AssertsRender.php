<?php
namespace Tests\Unit\Chart\Fixture;

use Coyote\Domain\Chart;
use PHPUnit\Framework\Assert;
use Tests\Unit\BaseFixture\Browser\Browser;

trait AssertsRender
{
    public abstract function name(): string;

    function assertExpectedImage(Chart $chart): void
    {
        $this->assertImageEquals(
            "/expected/{$this->name()}.png",
            $this->renderChart($chart));
    }

    function assertImageEquals(string $expectedImage, string $actualImage): void
    {
        if ($this->readExpectedImage($expectedImage) === $actualImage) {
            Assert::assertTrue(true);
        } else {
            $this->storeOverriddenImage($expectedImage, $actualImage);
            Assert::fail("Failed to assert that chart is rendered as expected in: $expectedImage (created actual file for comparison)");
        }
    }

    function renderChart(Chart $chart): string
    {
        $browser = new Browser();
        $browser->setHtmlSource("<html><body>$chart</body></html>");
        return $this->binaryImage($this->chartAsHtmlBase64($browser));
    }

    function chartAsHtmlBase64(Browser $browser): mixed
    {
        return $browser->execute("return Chart.getChart('chart').toBase64Image('image/png', 1);");
    }

    function binaryImage(string $htmlBase64): string
    {
        return \base64_decode($this->substringAfter($htmlBase64, ','));
    }

    function substringAfter(string $string, string $separator): string
    {
        return \subStr($string, \strPos($string, $separator) + 1);
    }

    function readExpectedImage(string $filename): string
    {
        return \file_get_contents(__DIR__ . '/../' . $filename) ?: '';
    }

    function storeOverriddenImage(string $filename, string $binary): void
    {
        \file_put_contents(
            __DIR__ . '/../' . $this->overriddenFilename($filename),
            $binary);
    }

    function overriddenFilename(string $expectedFile): string
    {
        return \str_replace('.png', '.actual.png', $expectedFile);
    }
}
