<?php
namespace Coyote\Domain\Administrator\Activity;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Coyote\Domain\Chart;
use Coyote\User;
use Coyote\View\Twig\TwigLiteral;

readonly class Activity
{
    public TwigLiteral $postsChart;
    public TwigLiteral $categoriesChart;
    public TwigLiteral $deleteReasonsChart;

    public TwigLiteral $chartLibrarySourceHtml;

    private Mention $mention;
    public array $categories;

    /**
     * @param Post[] $posts
     * @param Category[] $categories
     */
    public function __construct(
        private User $user,
        array        $postDates,
        public array $posts,
        array        $categories,
        public array $deleteReasons,
    )
    {
        $segments = new Segments($postDates);
        $this->mention = new Mention($user);

        $postsChart = new Chart(
            $segments->dates(),
            $segments->peeks(),
            ['#ff9f40'],
            'posts-chart',
            baseline:40,
        );

        \uSort($categories, fn(Category $a, Category $b): int => $b->posts - $a->posts);
        $this->categories = $categories;

        $forumNames = $this->extracted($this->categories, 'forumName');
        $categoriesChart = new Chart(
            $forumNames,
            $this->extracted($this->categories, 'posts'),
            \array_map($this->categoryColor(...), $forumNames),
            'categories-chart',
            baseline:40,
            horizontal:true,
        );

        $deleteReasonsChart = new Chart(
            \array_map(
                fn(?string $reason) => $reason ?? '(nie podano powodu)',
                $this->extracted($this->deleteReasons, 'reason'),
            ),
            $this->extracted($this->deleteReasons, 'posts'),
            ['#ff6384'],
            'reasons-chart',
            baseline:10,
            horizontal:true,
        );

        $this->postsChart = new TwigLiteral($postsChart);
        $this->categoriesChart = new TwigLiteral($categoriesChart);
        $this->deleteReasonsChart = new TwigLiteral($deleteReasonsChart);

        $this->chartLibrarySourceHtml = new TwigLiteral($postsChart->librarySourceHtml());
    }

    public function hasDeleteReasons(): bool
    {
        return \count($this->deleteReasons) > 0;
    }

    public function hasAnyPosts(): bool
    {
        return \count($this->posts) > 0;
    }

    public function username(): string
    {
        return $this->user->name;
    }

    public function mention(): TwigLiteral
    {
        return $this->mention->mention();
    }

    public function accountCreatedAt(): string
    {
        return $this->user->created_at->format('Y-m-d H:i:s');
    }

    public function extracted(array $array, string $field): array
    {
        $values = [];
        foreach ($array as $category) {
            $values[] = $category->$field;
        }
        return $values;
    }

    public function createdAgoMajor(): string
    {
        [$number, $unit] = \explode(' ', $this->createdAgo(), 3);
        return "$number $unit";
    }

    public function createdAgoMinor(): string
    {
        return \subStr($this->createdAgo(), \strLen($this->createdAgoMajor()));
    }

    private function createdAgo(): string
    {
        $carbonInterval = $this->user->created_at->diff(Carbon::now());
        return $this->firstWords($carbonInterval, 6) . " temu";
    }

    private function firstWords(CarbonInterval $interval, int $words): string
    {
        $pieces = \explode(' ', $interval);
        return \implode(' ', \array_slice($pieces, 0, $words));
    }

    private function categoryColor(string $forumName): string
    {
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
            return '#c9cbcf'; // gray
        }
        if ($forumName === 'Ogłoszenia_drobne') {
            return '#36a2eb'; // blue
        }
        return '#ff9f40'; // orange
    }
}
