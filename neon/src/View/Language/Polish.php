<?php
namespace Neon\View\Language;

class Polish implements Language
{
    private array $phrases = [
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
