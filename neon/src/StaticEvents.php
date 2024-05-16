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
            new Domain\Event\Event(
                'Kościuszkon',
                'Kraków',
                true,
                ['hackaton', 'studenci'],
                new Date(2024, 6, 8),
                EventKind::Hackaton,
                'https://kosciuszkon.pk.edu.pl/',
                'https://4programmers.net/Mikroblogi/View/141749',
            ),
            new Event(
                'Code Europe',
                'Kraków',
                false,
                ['dane', 'qa', 'produkt'],
                new Date(2024, 6, 10),
                EventKind::Conference,
                'https://www.codeeurope.pl/pl/',
                'https://4programmers.net/Mikroblogi/View/141355',
            ),
            new Event(
                'BeefUp',
                'Katowice',
                false,
                ['networking', 'biznes'],
                new Date(2024, 6, 13),
                EventKind::Meetup,
                'https://www.beefup.pro/',
                'https://4programmers.net/Mikroblogi/View/141784',
            ),
        ];
    }
}
