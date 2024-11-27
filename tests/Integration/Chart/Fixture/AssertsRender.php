<?php
namespace Tests\Integration\Chart\Fixture;

use Coyote\Domain\View\Chart;
use PHPUnit\Framework\Assert;

trait AssertsRender
{
    public abstract function name(): string;

    function assertExpectedImage(Chart $chart): void
    {
        $view = new ChartView();
        $view->renderCharts([$chart]);
        $this->assertImageEquals(
            "/expected/{$this->name()}.png",
            $view->chartImage($chart->id));
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

    function renderedCharts(array $charts): ChartView
    {
        $view = new ChartView();
        $view->renderCharts($charts);
        return $view;
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
