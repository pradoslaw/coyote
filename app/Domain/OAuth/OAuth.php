<?php
namespace Coyote\Domain\OAuth;

interface OAuth
{
    public function loginUrl(string $provider): string;

    public function user(string $provider): User;
}
