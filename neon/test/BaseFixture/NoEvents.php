<?php
namespace Neon\Test\BaseFixture;

use Neon\Persistence\Events;

class NoEvents implements Events
{
    public function fetchEvents(): array
    {
        return [];
    }
}
