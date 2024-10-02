<?php
namespace Coyote\Domain\Online;

readonly class Viewers
{
    public function __construct(public array $users, public int $guestsCount)
    {
    }
}
