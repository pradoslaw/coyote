<?php
namespace Coyote\Domain;

class Chart
{
    private array $options;

    public function __construct(
        string $chartTitle,
        array  $labels,
        array  $values,
        string $hexColor,
    )
    {
        [$r, $g, $b] = $this->rgb($hexColor);
        $this->options = [
            'type'    => 'bar',
            'data'    => [
                'labels'   => $labels,
                'datasets' => [
                    [
                        'label'           => $chartTitle,
                        'data'            => $values,
                        'backgroundColor' => "rgba($r, $g, $b, 0.2)",
                        'borderColor'     => "rgb($r, $g, $b)",
                        'borderWidth'     => 1,
                    ],
                ],
            ],
            'options' => [
                'animation' => false,
            ],
        ];
    }

    private function rgb(string $hexColor): array
    {
        return \sScanF($hexColor, '#%02x%02x%02x');
    }

    public function __toString(): string
    {
        return <<<html
            <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
            <canvas id="chart"></canvas>
            <script>new Chart(document.getElementById("chart"), {$this->options()});</script>
            html;
    }

    private function options(): string
    {
        return \json_encode($this->options);
    }
}
