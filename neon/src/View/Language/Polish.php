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

        'Users' => 'Użytkowników',

        'Events'                    => 'Wydarzenia',
        'Incoming events'           => 'Nadchodzące wydarzenia',
        'Events with our patronage' => 'Wydarzenia z naszym patronatem',

        'Conference' => 'Konferencja',
        'Hackaton'   => 'Hackaton',
        'Workshop'   => 'Warsztaty',

        'Free' => 'Bezpłatne',
        'Paid' => 'Płatne',
    ];

    public function t(string $phrase): string
    {
        if (\array_key_exists($phrase, $this->phrases)) {
            return $this->phrases[$phrase];
        }
        throw new \Exception("Failed to translate phrase: '$phrase'.");
    }
}
