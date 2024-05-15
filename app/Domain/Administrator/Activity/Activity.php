<?php
namespace Coyote\Domain\Administrator\Activity;

use Carbon\Carbon;
use Coyote\Domain\Chart;
use Coyote\User;
use Coyote\View\Twig\TwigLiteral;

readonly class Activity
{
    public TwigLiteral $postsChart;
    public TwigLiteral $chartLibrarySourceHtml;

    private Mention $mention;

    /**
     * @param Post[] $posts
     */
    public function __construct(
        private User $user,
        array        $postDates,
        public array $posts,
    )
    {
        $segments = new Segments($postDates);
        $this->mention = new Mention($user);
        $chart = new Chart(
            'Posty',
            $segments->dates(),
            $segments->peeks(),
            ['#ff9f40'],
            'posts-chart',
        );
        $this->postsChart = new TwigLiteral($chart);
        $this->chartLibrarySourceHtml = new TwigLiteral($chart->librarySourceHtml());
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

    public function createdAgo(): string
    {
        $carbonInterval = $this->user->created_at->diff(Carbon::now());
        return "$carbonInterval temu";
    }
}
