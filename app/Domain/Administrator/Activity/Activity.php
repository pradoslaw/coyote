<?php
namespace Coyote\Domain\Administrator\Activity;

use Carbon\Carbon;
use Coyote\Domain\Chart;
use Coyote\User;
use Coyote\View\Twig\TwigLiteral;

readonly class Activity
{
    public TwigLiteral $postsChart;
    public TwigLiteral $categoriesChart;

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
    )
    {
        $segments = new Segments($postDates);
        $this->mention = new Mention($user);

        $postsChart = new Chart(
            'Posty',
            $segments->dates(),
            $segments->peeks(),
            ['#ff9f40'],
            'posts-chart',
        );

        \uSort($categories, fn(Category $a, Category $b): int => $b->posts - $a->posts);
        $this->categories = $categories;

        $categoriesChart = new Chart(
            'Posty w kategoriach',
            $this->categoryNames(),
            $this->categoryStats(),
            ['#ff6384', '#ff9f40', '#ffcd56', '#4bc0c0', '#36a2eb', '#9966ff', '#c9cbcf'],
            'categories-chart',
            horizontal:true,
        );

        $this->postsChart = new TwigLiteral($postsChart);
        $this->categoriesChart = new TwigLiteral($categoriesChart);

        $this->chartLibrarySourceHtml = new TwigLiteral($postsChart->librarySourceHtml());
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

    public function categoryNames(): array
    {
        $names = [];
        /** @var Category $category */
        foreach ($this->categories as $category) {
            $names[] = $category->forumName;
        }
        return $names;
    }

    public function categoryStats(): array
    {
        $posts = [];
        /** @var Category $category */
        foreach ($this->categories as $category) {
            $posts[] = $category->posts;
        }
        return $posts;
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
        return "$carbonInterval temu";
    }
}
