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
                ['software', 'hardware'],
                new Date(2024, 4, 16),
                EventKind::Conference,
                'https://4developers.org.pl/',
                'https://4programmers.net/Mikroblogi/View/140933',
            ),
            new Event(
                'Best Hacking League',
                'Warszawa',
                true,
                ['software', 'hardware', 'ai', 'cybersecurity'],
                new Date(2024, 4, 20),
                EventKind::Hackaton,
                'https://besthackingleague.pl/',
                'https://4programmers.net/Mikroblogi/View/141082/',
            ),
            new Event(
                '18 Sesja Linuksowa',
                'Wrocław',
                true,
                ['linux', 'opensource'],
                new Date(2024, 4, 20),
                EventKind::Conference,
                'https://18.sesja.linuksowa.pl/',
                'https://4programmers.net/Mikroblogi/View/141364/',
            ),
            new Event(
                'SForce Summit 2024',
                'Online',
                true,
                ['salesforce'],
                new Date(2024, 4, 23),
                EventKind::Conference,
                'https://sforcesummit.pl/',
                'https://4programmers.net/Mikroblogi/View/141331',
            ),
            new Event(
                'Founders Mind VII',
                'Warszawa',
                false,
                ['biznes', 'networking'],
                new Date(2024, 5, 14),
                EventKind::Conference,
                'https://foundersmind.pl/#bilety',
                'https://4programmers.net/Mikroblogi/View/141058/',
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
        ];
    }
}
