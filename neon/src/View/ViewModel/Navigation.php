<?php
namespace Neon\View\ViewModel;

readonly class Navigation
{
    /** @var Link[] */
    public array $links;

    public function __construct(
        public string $homepageUrl,
        public array  $items,
        public string $githubUrl,
        public string $githubStarsUrl,
        public string $githubName,
        public string $githubStars,
        array         $controls,
    )
    {
        if (empty($controls)) {
            $this->links = [];
        } else {
            [$big, $small] = \array_keys($controls);
            $this->links = [
                new Link($big, $controls[$big], true),
                new Link($small, $controls[$small], false),
            ];
        }
    }
}
