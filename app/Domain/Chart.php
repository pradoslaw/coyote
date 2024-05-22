<?php
namespace Coyote\Domain;

class Chart
{
    private array $options;

    public function __construct(
        string $chartTitle,
        array  $labels,
        array  $values,
        array  $hexColors,
        bool   $horizontal = false,
    )
    {
        [$fillColors, $borderColors] = $this->colors($hexColors);
        $this->options = [
            'type'    => 'bar',
            'data'    => [
                'labels'   => $labels,
                'datasets' => [
                    [
                        'label'           => $chartTitle,
                        'data'            => $values,
                        'backgroundColor' => $fillColors,
                        'borderColor'     => $borderColors,
                        'borderWidth'     => 1,
                    ],
                ],
            ],
            'options' => [
                'animation'           => false,
                'maintainAspectRatio' => false,
                'indexAxis'           => $horizontal ? 'y' : 'x',
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

    public function __toString(): string
    {
        return <<<html
            <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
            <div style="height:inherit;">
                <canvas id="chart"></canvas>
            </div>
            <script>new Chart(document.getElementById("chart"), {$this->options()});</script>
            html;
    }

    private function options(): string
    {
        return \json_encode($this->options);
    }
}
