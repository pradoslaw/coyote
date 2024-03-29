<?php
namespace Neon\View\ViewModel;

readonly class Navigation
{
    public function __construct(
        public string $homepageUrl,
        public array  $items,
        public string $githubUrl,
        public string $githubStarsUrl,
        public string $githubName,
        public string $githubStars,
        public array  $controls,
    )
    {
    }
}
