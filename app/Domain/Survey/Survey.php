<?php
namespace Coyote\Domain\Survey;

use Coyote\Services\Guest;

readonly class Survey
{
    public function __construct(private Guest $guest)
    {
    }

    public function setState(string $state): void
    {
        $this->guest->setSetting('surveyState', $state);
    }

    public function state(): string
    {
        return $this->guest->getSetting('surveyState', 'survey-none');
    }
}
