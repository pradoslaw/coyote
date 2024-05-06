<?php
namespace Neon\Test\BaseFixture\Domain;

use Neon\Persistence\System;

readonly class NoSystem implements System
{
    public function __construct(private bool $darkTheme = false)
    {
    }

    public function darkTheme(): bool
    {
        return $this->darkTheme;
    }
}
