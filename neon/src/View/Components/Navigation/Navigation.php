<?php
namespace Neon\View\Components\Navigation;

use Neon\Domain\Visitor;
use Neon\View\Language\Language;

readonly class Navigation
{
    /** @var Link[] */
    public array $links;
    public string $avatarUrl;
    public bool $avatarVisible;
    public bool $canLogout;
    public string $logoutTitle;

    public function __construct(
        Language      $language,
        public string $homepageUrl,
        public array  $items,
        public string $githubUrl,
        public string $githubStarsUrl,
        public string $githubName,
        public string $githubStars,
        array         $controls,
        Visitor       $visitor,
        public string $csrf,
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
        $this->logoutTitle = $language->t('Logout');
    }
}
