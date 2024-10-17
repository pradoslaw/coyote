<?php
namespace Coyote\Http\Controllers\Adm;

use Carbon\Carbon;
use Coyote\Domain\Registration\HistoryRange;
use Coyote\Domain\Registration\Period;
use Coyote\Domain\Registration\UserRegistrations;
use Coyote\Domain\StringHtml;
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

            'registrationsChartWeeks'  => $this->historyChartHtml($registrations, Period::Week, 'created_at'),
            'registrationsChartMonths' => $this->historyChartHtml($registrations, Period::Month, 'created_at'),
            'registrationsChartYears'  => $this->historyChartHtml($registrations, Period::Year, 'created_at'),

            'leaveChartWeeks'  => $this->historyChartHtml($registrations, Period::Week, 'visited_at'),
            'leaveChartMonths' => $this->historyChartHtml($registrations, Period::Month, 'visited_at'),
            'leaveChartYears'  => $this->historyChartHtml($registrations, Period::Year, 'visited_at'),
        ]);
    }

    private function historyChartHtml(UserRegistrations $registrations, Period $period, string $column): StringHtml
    {
        return new StringHtml($this->view('adm.registrations-chart', [
            'chart'              => $this->registrationsChart($registrations, $period, $column),
            'chartLibrarySource' => Chart::librarySourceHtml(),
            'title'              => $column === 'created_at' ? 'Historia rejestracji' : 'Ostatnia wizyta użytkownika',
        ]));
    }

    private function registrationsChart(UserRegistrations $registrations, Period $period, string $column): Chart
    {
        $range = new HistoryRange($this->dateNow(), $period, 30);
        return $this->chart(
            "$period->name.$column",
            $registrations->inRangeColumn($range, $column),
            $column === 'created_at',
        );
    }

    private function dateNow(): string
    {
        return Carbon::now()->toDateString();
    }

    private function chart(string $chartId, array $registeredUsers, bool $positive): Chart
    {
        return new Chart(
            \array_keys($registeredUsers),
            \array_values($registeredUsers),
            [$positive ? '#ff9f40' : '#ff0000'],
            "registration-history-chart-$chartId",
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
