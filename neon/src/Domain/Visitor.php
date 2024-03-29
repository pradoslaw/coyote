<?php
namespace Neon\Domain;

interface Visitor
{
    public function loggedInUserAvatarUrl(): ?string;
}
