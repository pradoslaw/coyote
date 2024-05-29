<?php
namespace Coyote\Domain\Administrator\Activity;

use Coyote\User;
use Coyote\View\Twig\TwigLiteral;

class Mention
{
    public static function of(User $user): Mention
    {
        return new Mention($user);
    }

    private function __construct(private User $user)
    {
    }

    public function mention(): TwigLiteral
    {
        $url = route('profile', [$this->user->id]);
        return new TwigLiteral('<a class="mention" href="' . \htmlSpecialChars($url) . '">' . '@' . $this->user->name . '</a>');
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
