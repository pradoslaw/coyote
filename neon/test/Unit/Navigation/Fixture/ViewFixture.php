<?php
namespace Neon\Test\Unit\Navigation\Fixture;

use Neon;
use Neon\Test\BaseFixture\ItemView;
use Neon\View\Html\Body\Navigation;

trait ViewFixture
{
    function navigation(array $fields): ItemView
    {
        return new ItemView(new Navigation($this->viewModel($fields)));
    }

    function viewModel(array $fields): Neon\View\ViewModel\Navigation
    {
        return new Neon\View\ViewModel\Navigation(
            $fields['homepageUrl'] ?? '',
            $fields['items'] ?? [],
            $fields['githubUrl'] ?? '',
            $fields['githubStarsUrl'] ?? '',
            $fields['githubName'] ?? '',
            $fields['githubStars'] ?? -1,
            $fields['controls'] ?? [],
            isset($fields['loggedInAvatarUrl'])
                ? LoggedInUser::withAvatar($fields['loggedInAvatarUrl'])
                : LoggedInUser::guest(),
        );
    }
}
