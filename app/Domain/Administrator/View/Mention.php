<?php
namespace Coyote\Domain\Administrator\View;

use Coyote\Domain\Html;
use Coyote\User;

class Mention extends Html
{
    public static function of(User $user): Mention
    {
        return new Mention($user->id, $user->name);
    }

    public function __construct(private int $userId, private string $userName)
    {
    }

    protected function toHtml(): string
    {
        $url = route('profile', [$this->userId]);
        return '<a class="mention" href="' . \htmlSpecialChars($url) . '">' . '@' . $this->userName . '</a>';
    }
}
