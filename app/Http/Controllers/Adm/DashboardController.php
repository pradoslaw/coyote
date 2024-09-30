<?php
namespace Coyote\Http\Controllers\Adm;

use Carbon\Carbon;
use Coyote\Domain\HistoryRange;
use Coyote\Domain\StringHtml;
use Coyote\Domain\UserRegistrations;
use Coyote\Domain\View\Chart;
use Illuminate\Foundation\Application;
use Illuminate\View\View;

class DashboardController extends BaseController
{
    public function index(UserRegistrations $registrations): View
    {
        return $this->view('adm.dashboard', [
            'checklist' => [
                $this->directoryWritable('storage/', \storage_path()),
                $this->directoryWritable('uploads/', \public_path()),
                [
                    'label' => 'Redis włączony',
                    'value' => \config('cache.default'),
                ],
                [
                    'label' => new StringHtml('PHP - <code>' . \PHP_VERSION . '</code>'),
                    'value' => true,
                ],
                [
                    'label' => new StringHtml('Laravel - <code>' . Application::VERSION . '</code>'),
                    'value' => true,
                ],
            ],

            'registrationsChartModule' => new StringHtml($this->view('adm.registrations-chart', [
                'chart'              => $this->registrationHistoryChart($registrations),
                'chartLibrarySource' => Chart::librarySourceHtml(),
            ])),
        ]);
    }

    private function registrationHistoryChart(UserRegistrations $registrations): Chart
    {
        $registeredUsers = $registrations->inRange(new HistoryRange(Carbon::now()->toDateString(), weeks:30));
        return new Chart(
            \array_keys($registeredUsers),
            \array_values($registeredUsers),
            ['#ff9f40'],
            'registration-history-chart',
            baseline:5,
        );
    }

    public function directoryWritable(string $basePath, string $path): array
    {
        $permission = \decOct(\filePerms($path) & 0777);
        return [
            'label' => new StringHtml("Katalog <code>$basePath</code> ma prawa do zapisu - <code>$permission</code>"),
            'value' => \is_writeable(\storage_path()),
        ];
    }
}
