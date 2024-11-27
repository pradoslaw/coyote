<?php
namespace Tests\Integration\Chart\Fixture;

use Tests\Integration\BaseFixture\Browser\Browser;

class ChartView
{
    private static ?Browser $cached = null;
    private Browser $browser;

    public function __construct()
    {
        if (self::$cached === null) {
            self::$cached = new Browser();
        }
        $this->browser = self::$cached;
    }

    public function renderCharts(array $charts): void
    {
        $this->browser->setHtmlSource($this->htmlSourceCode($charts));
    }

    private function htmlSourceCode(array $charts): string
    {
        $first = $charts[0];
        $charts = \implode($charts);
        return "<html><body>{$first->librarySourceHtml()}$charts</body></html>";
    }

    public function chartImage(string $id): string
    {
        return $this->binaryImage($this->chartAsHtmlBase64($id));
    }

    private function chartAsHtmlBase64(string $id): mixed
    {
        return $this->browser->execute("return Chart.getChart('$id').toBase64Image('image/png', 1);");
    }

    private function binaryImage(string $htmlBase64): string
    {
        return \base64_decode($this->substringAfter($htmlBase64, ','));
    }

    private function substringAfter(string $string, string $separator): string
    {
        return \subStr($string, \strPos($string, $separator) + 1);
    }

    public function chartExists(string $id): bool
    {
        return $this->browser->execute("return typeof Chart.getChart('$id') !== 'undefined';");
    }
}
