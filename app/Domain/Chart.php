<?php
namespace Coyote\Domain;

class Chart
{
    private array $options;

    public function __construct(
        array          $labels,
        array          $values,
        array          $hexColors,
        private string $id,
        bool           $horizontal = false,
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
                    'legend' => [
                        'display' => false,
                    ],
                ],
                'animation'           => false,
                'maintainAspectRatio' => false,
                'indexAxis'           => $horizontal ? 'y' : 'x',
                'scales'              => [
                    'y' => ['ticks' => ['autoSkip' => false]],
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
        return '<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>';
    }

    public function __toString(): string
    {
        return <<<html
            <div style="height:inherit;">
                <canvas id="$this->id"></canvas>
            </div>
            <script>new Chart(document.getElementById("$this->id"), {$this->options()});</script>
            html;
    }

    private function options(): string
    {
        return \json_encode($this->options);
    }
}
