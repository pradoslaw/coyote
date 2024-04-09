<?php
namespace Neon\Test\Unit\Navigation\Fixture;

use Neon;
use Neon\Test\BaseFixture\ItemView;
use Neon\View\Components\Navigation\NavigationHtml;

trait ViewFixture
{
    function navigation(array $fields): ItemView
    {
        return new ItemView(new NavigationHtml($this->viewModel($fields)));
    }

    function viewModel(array $fields): Neon\View\Components\Navigation\Navigation
    {
        return new Neon\View\Components\Navigation\Navigation(
            $fields['homepageUrl'] ?? '',
            $fields['items'] ?? [],
            $fields['githubUrl'] ?? '',
            $fields['githubStarsUrl'] ?? '',
            $fields['githubName'] ?? '',
            $fields['githubStars'] ?? -1,
            $fields['controls'] ?? [],
            $this->loggedIn($fields),
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
