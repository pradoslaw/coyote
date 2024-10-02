<?php
namespace Coyote\Domain\Online;

readonly class Viewers
{
    /**
     * @param ViewerUser[] $users
     */
    public function __construct(
        public array $users,
        public int   $guestsCount,
    )
    {
    }
}
