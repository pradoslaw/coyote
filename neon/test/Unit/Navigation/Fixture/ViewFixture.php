<?php
namespace Neon\Test\Unit\Navigation\Fixture;

use Neon\Test\BaseFixture\ItemView;
use Neon\View\Components\Navigation\Navigation;
use Neon\View\Components\Navigation\NavigationHtml;
use Neon\View\Language\English;
use Neon\View\Language\Language;

trait ViewFixture
{
    function navigation(array $fields): ItemView
    {
        return new ItemView(new NavigationHtml($this->viewModel($fields, new English())));
    }

    function viewModel(array $fields, Language $language): Navigation
    {
        return new Navigation(
            $language,
            $fields['homepageUrl'] ?? '',
            $fields['items'] ?? [],
            $fields['githubUrl'] ?? '',
            $fields['githubStarsUrl'] ?? '',
            $fields['githubName'] ?? '',
            $fields['githubStars'] ?? -1,
            $fields['controls'] ?? [],
            $this->loggedIn($fields),
            '',
        );
    }

    private function loggedIn(array $fields): LoggedInUser
    {
        if (isset($fields['loggedInAvatarUrl'])) {
            return LoggedInUser::withAvatar($fields['loggedInAvatarUrl']);
        }
        if (isset($fields['loggedIn'])) {
            return LoggedInUser::withoutAvatar();
        }
        return LoggedInUser::guest();
    }
}
