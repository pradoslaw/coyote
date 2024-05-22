<?php
namespace Coyote\Domain;

class Chart
{
    private string $options;

    public function __construct(
        private string $chartTitle,
        array          $labels,
        array          $values,
    )
    {
        $this->options = \json_encode([
            'type'    => 'bar',
            'data'    => [
                'labels'   => $labels,
                'datasets' => [
                    [
                        'label'       => $this->chartTitle,
                        'data'        => $values,
                        'borderWidth' => 1,
                    ],
                ],
            ],
            'options' => [
                'animation' => false,
            ],
        ], \JSON_PRETTY_PRINT);
    }

    public function __toString(): string
    {
        return <<<EOF
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
        <canvas id="chart"></canvas>
        <script>
            const chart = new Chart(document.getElementById("chart"), {$this->options});
        </script>
EOF;
    }
}
