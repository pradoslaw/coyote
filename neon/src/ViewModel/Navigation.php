<?php
namespace Neon\ViewModel;

readonly class Navigation
{
    public function __construct(
        public array  $items,
        public string $githubUrl,
        public string $githubName,
        public string $githubStars,
        public array  $controls,
    )
    {
    }
}
