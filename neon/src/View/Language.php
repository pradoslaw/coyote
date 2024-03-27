<?php
namespace Neon\View;

class Language
{
    private array $phrases = [
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
