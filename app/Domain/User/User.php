<?php
namespace Coyote\Domain\User;

class User
{
    public function __construct(
        public bool $loggedIn,
        public ?int $id
    ) {
    }
}
