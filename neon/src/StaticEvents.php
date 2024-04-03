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
            new Event(
                '4DEVELOPERS',
                'Warszawa',
                false,
                ['Software', 'Hardware'],
                new Date(2024, 4, 16),
                EventKind::Conference,
            ),
            new Event(
                'Best Hacking League',
                'Warszawa',
                true,
                ['Software', 'Hardware', 'AI', 'Cybersecurity'],
                new Date(2024, 4, 20),
                EventKind::Hackaton,
            ),
            new Event(
                'Founders Mind VII',
                'Warszawa',
                false,
                ['Biznes', 'Networking'],
                new Date(2024, 5, 14),
                EventKind::Conference,
            ),
        ];
    }
}
