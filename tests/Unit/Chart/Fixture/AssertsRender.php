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
        return $this->binaryImage($this->renderChartToHtmlBase64($chart));
    }

    private function renderChartToHtmlBase64(Chart $chart): string
    {
        $browser = $this->newBrowserWithRenderedCharts([$chart]);
        return $this->chartAsHtmlBase64($browser, 'chart');
    }

    function chartAsHtmlBase64(Browser $browser, string $id): mixed
    {
        return $browser->execute("return Chart.getChart('$id').toBase64Image('image/png', 1);");
    }

    function chartExists(Browser $browser, string $id): bool
    {
        return $browser->execute("return typeof Chart.getChart('$id') !== 'undefined';");
    }

    function newBrowserWithRenderedCharts(array $charts): Browser
    {
        $first = $charts[0];
        $charts = \implode($charts);
        $browser = new Browser();
        $browser->setHtmlSource("<html><body>{$first->librarySourceHtml()}$charts</body></html>");
        return $browser;
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
