<?php
namespace Coyote\Domain\Administrator\View;

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
}
