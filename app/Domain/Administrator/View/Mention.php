<?php
namespace Coyote\Domain\Administrator\View;

use Coyote\Domain\Html;
use Coyote\User;

class Mention extends Html
{
    public static function of(User $user): Mention
    {
        return new Mention($user);
    }

    private function __construct(private User $user)
    {
    }

    protected function toHtml(): string
    {
        $url = route('profile', [$this->user->id]);
        return '<a class="mention" href="' . \htmlSpecialChars($url) . '">' . '@' . $this->user->name . '</a>';
    }
}
