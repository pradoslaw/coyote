<?php
namespace Neon\Domain;

interface Visitor
{
    public function loggedIn(): bool;

    public function loggedInUserAvatarUrl(): ?string;
}
