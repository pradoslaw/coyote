<?php
namespace Neon\Test\BaseFixture\Domain;

use Neon\Persistence\Events;

class NoEvents implements Events
{
    public function fetchEvents(): array
    {
        return [];
    }
}
