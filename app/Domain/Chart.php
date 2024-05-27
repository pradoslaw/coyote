<?php
namespace Coyote\Domain;

class Chart
{
    private array $options;

    public function __construct(
        private array  $labels,
        array          $values,
        array          $hexColors,
        private string $id,
        int            $baseline = 1,
        private bool   $horizontal = false,
    )
    {
        [$fillColors, $borderColors] = $this->colors($hexColors);
        $this->options = [
            'type'    => 'bar',
            'data'    => [
                'labels'   => $labels,
                'datasets' => [
                    [
                        'data'            => $values,
                        'backgroundColor' => $fillColors,
                        'borderColor'     => $borderColors,
                        'borderWidth'     => 1,
                    ],
                ],
            ],
            'options' => [
                'plugins'             => [
                    'legend'     => ['display' => false],
                    'datalabels' => [
                        'anchor' => 'end',
                        'align'  => $horizontal ? 'right' : 'top',
                    ],
                ],
                'animation'           => false,
                'maintainAspectRatio' => false,
                'indexAxis'           => $horizontal ? 'y' : 'x',
                'scales'              => [
                    'y' => [
                        'ticks'        => ['autoSkip' => false],
                        'suggestedMax' => $baseline,
                    ],
                    'x' => [
                        'suggestedMax' => $baseline,
                    ],
                ],
            ],
        ];
    }

    private function colors(array $hexColors): array
    {
        $fillColors = [];
        $borderColors = [];
        foreach ($hexColors as $hexColor) {
            [$r, $g, $b] = $this->rgb($hexColor);
            $fillColors[] = "rgba($r, $g, $b, 0.2)";
            $borderColors[] = "rgb($r, $g, $b)";
        }
        return [$fillColors, $borderColors];
    }

    private function rgb(string $hexColor): array
    {
        return \sScanF($hexColor, '#%02x%02x%02x');
    }

    public function librarySourceHtml(): string
    {
        return '<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>' .
            '<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>';
    }

    public function __toString(): string
    {
        return <<<html
            <div style="height:{$this->canvasHeight()}px;">
                <canvas id="$this->id"></canvas>
            </div>
            <script>new Chart(document.getElementById("$this->id"), {...{$this->options()}, plugins:[ChartDataLabels]});</script>
            html;
    }

    private function canvasHeight(): int
    {
        if ($this->horizontal) {
            return $this->horizontalItems() * 32;
        }
        return 280;
    }

    private function horizontalItems(): int
    {
        return \count($this->labels) + 1;
    }

    private function options(): string
    {
        return \json_encode($this->options);
    }
}
