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
                'Warsaw Salesforce Meetup #5',
                'Warszawa',
                true,
                ['salesforce'],
                new Date(2024, 5, 15),
                EventKind::Conference,
                'https://evenea.pl/pl/wydarzenie/sforcewarsaw5',
                'https://4programmers.net/Mikroblogi/View/141687',
            ),
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
        ];
    }
}
