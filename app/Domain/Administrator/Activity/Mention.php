<?php
namespace Coyote\Domain\Administrator\Activity;

use Coyote\User;
use Coyote\View\Twig\TwigLiteral;

class Mention
{
    public function __construct(private User $user)
    {
    }

    public function mention(): TwigLiteral
    {
        $url = route('profile', [$this->user->id]);
        return new TwigLiteral('<a class="mention" href="' . \htmlSpecialChars($url) . '">' . $this->mentionString() . '</a>');
    }

    public function mentionString(): string
    {
        $username = $this->user->name;
        if ($this->containsAnyOf($username, '. ()')) {
            return "@{{$username}}";
        }
        return '@' . $username;
    }

    private function containsAnyOf(string $string, string $characters): bool
    {
        return \strpbrk($string, $characters) !== false;
    }
}
