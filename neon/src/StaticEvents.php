<?php
namespace Neon;

use Neon\Domain\Event\Date;
use Neon\Domain\Event\Event;
use Neon\Domain\Event\EventKind;
use Neon\Persistence\Events;

class StaticEvents implements Events
{
    public function fetchEvents(): array
    {
        return [
        ];
    }
}
