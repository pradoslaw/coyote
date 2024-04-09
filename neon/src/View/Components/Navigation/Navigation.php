<?php
namespace Neon\View\Components\Navigation;

use Neon\Domain\Visitor;

readonly class Navigation
{
    /** @var Link[] */
    public array $links;
    public string $avatarUrl;
    public bool $avatarVisible;
    public bool $canLogout;

    public function __construct(
        public string $homepageUrl,
        public array  $items,
        public string $githubUrl,
        public string $githubStarsUrl,
        public string $githubName,
        public string $githubStars,
        array         $controls,
        Visitor       $visitor,
    )
    {
        if (empty($controls) || $visitor->loggedIn()) {
            $this->links = [];
        } else {
            [$big, $small] = \array_keys($controls);
            $this->links = [
                new Link($big, $controls[$big], true),
                new Link($small, $controls[$small], false),
            ];
        }
        $this->avatarVisible = $visitor->loggedIn();
        $this->canLogout = $visitor->loggedIn();
        $this->avatarUrl = $visitor->loggedInUserAvatarUrl() ?? '/neon/avatarPlaceholder.png';
    }
}
