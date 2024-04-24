<?php
namespace Neon\View\Language;

class Polish implements Language
{
    private array $phrases = [
        'Forum'      => 'Forum',
        'Microblogs' => 'Mikroblogi',
        'Jobs'       => 'Praca',
        'Wiki'       => 'Kompendium',

        'Create account' => 'Utwórz konto',
        'Login'          => 'Logowanie',
        'Logout'         => 'Wyloguj',

        'Users' => 'Użytkowników',

        'Events'                    => 'Wydarzenia',
        'Incoming events'           => 'Nadchodzące wydarzenia',
        'Events with our patronage' => 'Wydarzenia z naszym patronatem',

        'Search for jobs' => 'Szukaj pracy',
        'Not provided'    => 'Nie podano',
        'Remote work'     => 'Praca zdalna',
        'cities'          => 'miast',

        'Conference' => 'Konferencja',
        'Hackaton'   => 'Hackaton',
        'Workshop'   => 'Warsztaty',

        'Free' => 'Bezpłatne',
        'Paid' => 'Płatne',

        'Mon' => 'Pn',
        'Tue' => 'Wt',
        'Wed' => 'Śr',
        'Thu' => 'Cz',
        'Fri' => 'Pt',
        'Sat' => 'Sb',
        'Sun' => 'Nd',

        'Are you hosting an event?'                                               =>
            'Organizujesz wydarzenie?',
        "Add your event to our calendar and we'll gladly provide media support." =>
            'Zarejestruj je w naszym kalendarzu, a z radością udzielimy mu wsparcia medialnego.',
        'Learn more'                                                              =>
            'Dowiedz się więcej',
    ];

    public function t(string $phrase): string
    {
        if (\array_key_exists($phrase, $this->phrases)) {
            return $this->phrases[$phrase];
        }
        throw new \Exception("Failed to translate phrase: '$phrase'.");
    }

    public function dec(int $plurality, string $noun): string
    {
        if ($plurality === 1) {
            return 'miasto';
        }
        if ($plurality > 4) {
            return 'miast';
        }
        return 'miasta';
    }
}
