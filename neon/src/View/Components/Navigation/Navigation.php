<?php
namespace Neon\View\Components\Navigation;

use Neon\Domain\Visitor;
use Neon\View\Language\Language;

readonly class Navigation
{
    public array $items;
    /** @var Link[] */
    public array $links;
    public string $avatarUrl;
    public bool $avatarVisible;
    public bool $canLogout;
    public string $logoutTitle;
    public string $searchBarTitle;

    public function __construct(
        private Language $language,
        public string    $homepageUrl,
        array            $items,
        public string    $githubUrl,
        public string    $githubStarsUrl,
        public string    $githubName,
        public string    $githubStars,
        array            $controls,
        Visitor          $visitor,
        public string    $csrf,
    )
    {
        if (\count($controls) !== 2) {
            $this->links = [];
        } else {
            [$big, $small] = \array_keys($controls);
            $this->links = [
                new Link($this->language->t($big), $controls[$big], true),
                new Link($this->language->t($small), $controls[$small], false),
            ];
        }
        $this->items = $this->translateKeys($items);
        $this->avatarVisible = $visitor->loggedIn();
        $this->canLogout = $visitor->loggedIn();
        $this->avatarUrl = $visitor->loggedInUserAvatarUrl() ?? '/neon/avatarPlaceholder.png';
        $this->logoutTitle = $language->t('Logout');
        $this->searchBarTitle = $language->t('Search threads, posts or users');
    }

    private function translateKeys(array $items): array
    {
        return \array_combine(
            \array_map($this->language->t(...), \array_keys($items)),
            $items,
        );
    }
}
