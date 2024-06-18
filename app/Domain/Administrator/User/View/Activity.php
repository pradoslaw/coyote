<?php
namespace Coyote\Domain\Administrator\User\View;

use Coyote\Domain\Administrator\User\Store\Category;
use Coyote\Domain\Administrator\User\Store\DeleteReason;
use Coyote\Domain\Administrator\User\Store\ReportReason;
use Coyote\Domain\Chart;
use Coyote\Domain\Html;
use Coyote\Domain\PostStatistic;

/**
 * @deprecated This is both a presenter and a view object, we should split it
 */
readonly class Activity
{
    public Chart $categoriesChart;
    public Chart $deleteReasonsChart;
    public Chart $reportReasonsChart;
    public Html $chartLibrarySource;

    /**
     * @param Category[] $categories
     */
    public function __construct(
        array                $categories,
        array                $deleteReasons,
        array                $reportReasons,
        public PostStatistic $postsStatistic,
    )
    {
        $this->categoriesChart = $this->categoriesChart($this->categoriesSliced($this->categoriesSorted($categories), 10));
        $this->deleteReasonsChart = $this->deleteReasonsChart($this->reasonsSorted($deleteReasons));
        $this->reportReasonsChart = $this->reportReasonsChart($this->reportReasonsSorted($reportReasons));
        $this->chartLibrarySource = Chart::librarySourceHtml();
    }

    private function deleteReasonsChart(array $array): Chart
    {
        return new Chart(
            \array_map(
                fn(?string $reason) => $reason ?? '(nie podano powodu)',
                array_map(fn($object) => $object->reason, $array),
            ),
            array_map(fn($object) => $object->posts, $array),
            \array_map($this->deleteReasonColor(...), $array),
            'delete-reasons-chart',
            baseline:40,
            horizontal:true,
        );
    }

    private function categoriesChart(array $categories): Chart
    {
        return new Chart(
            array_map(fn($category) => $category->forumName ?? '(pozostałe)', $categories),
            array_map(fn($category) => $category->posts, $categories),
            \array_map($this->categoryColor(...), $categories),
            'categories-chart',
            baseline:40,
            horizontal:true,
        );
    }

    private function categoryColor(Category $category): string
    {
        if ($category->forumName === null) {
            return '#c9cbcf'; // gray
        }
        $forumName = $category->forumName;
        if (\in_array($forumName, ['Flame', 'Off-Topic', 'Kosz', 'Spolecznosc', 'Spolecznosc/Perełki', 'Moderatorzy/Kapownik'])) {
            return '#ff6384'; // red
        }
        if (\in_array($forumName, ['Kadra', 'Moderatorzy', 'Moderatorzy/Administracja', 'Moderatorzy/Kartoteka'])) {
            return '#9966ff'; // purple
        }
        if (\in_array($forumName, ['Kariera', 'Opinie_o_pracodawcach', 'CV_do_oceny'])) {
            return '#4bc0c0'; // cyan
        }
        if (\in_array($forumName, ['Archiwum', 'Archiwum/Yosemite', 'Archiwum/RoadRunner', 'Coyote', 'Coyote/Test', 'Spolecznosc/Projekty', 'Moderatorzy/Zapomniane'])) {
            return '#80a41a'; // gray
        }
        if ($forumName === 'Ogłoszenia_drobne') {
            return '#36a2eb'; // blue
        }
        return '#ff9f40'; // orange
    }

    /**
     * @param DeleteReason[] $reasons
     */
    private function reasonsSorted(array $reasons): array
    {
        \uSort($reasons, function (DeleteReason $a, DeleteReason $b): int {
            if ($a->reason === null) {
                return 1;
            }
            if ($b->reason === null) {
                return -1;
            }
            return $b->posts - $a->posts;
        });
        return $reasons;
    }

    private function deleteReasonColor(DeleteReason $reason): string
    {
        if ($reason->reason === null) {
            return '#c9cbcf'; // gray
        }
        if (\in_array($reason->reason, [
            'Spam', 'Trolling', 'Wulgaryzmy', 'Omijanie bana',
            'Wycieczki osobiste i/lub obrażanie innych użytkowników',
        ])) {
            return '#ff6384'; // red 
        }
        return '#ff9f40'; // orange
    }

    private function categoriesSorted(array $categories): array
    {
        \uSort($categories, fn(Category $a, Category $b): int => $b->posts - $a->posts);
        return $categories;
    }

    private function categoriesSliced(array $categories, int $importantAmount): array
    {
        if (\count($categories) < $importantAmount + 2) {
            return $categories;
        }
        $dumped = \array_slice($categories, $importantAmount);
        $remaining = 0;
        foreach ($dumped as $category) {
            $remaining += $category->posts;
        }
        $important = \array_slice($categories, 0, $importantAmount);
        $important[] = new Category(null, $remaining);
        return $important;
    }

    private function reportReasonsChart(array $reportReasons): Chart
    {
        return new Chart(
            \array_map(fn(ReportReason $reason) => $reason->reason, $reportReasons),
            \array_map(fn(ReportReason $reason) => $reason->count, $reportReasons),
            ['#ff6384'],
            id:'report-reasons-chart',
            baseline:40,
            horizontal:true,
        );
    }

    private function reportReasonsSorted(array $reportReasons): array
    {
        $order = \array_flip(['Spam', 'Wulgaryzmy', 'Off-Topic', 'Nieprawidłowa kategoria', 'Próba wyłudzenia gotowca', 'Inne']);
        \uSort($reportReasons, fn(ReportReason $a, ReportReason $b): int => -$order[$b->reason] + $order[$a->reason]);
        return $reportReasons;
    }
}
