<?php
namespace Coyote\Domain\OAuth;

class User
{
    public function __construct(
        public string  $providerId,
        public string  $email,
        public string  $name,
        public ?string $photoUrl,
    )
    {
    }
}
