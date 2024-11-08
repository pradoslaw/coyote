<?php
namespace Coyote\Feature\Trial;

use Coyote\Services\Guest;

readonly class TrialService
{
    public function __construct(private Guest $guest)
    {
    }

    public function isChoiceModern(): bool
    {
        return $this->guest->getSetting('isHomepageNew', 'choice-pending') === 'choice-modern';
    }

    public function setChoice(string $choice): void
    {
        $this->guest->setSetting('isHomepageNew',  "choice-$choice");
    }

    public function setStage(string $stage): void
    {
    }

    public function logPreview(string $choice): void
    {
    }

    public function setBadgeNarrow(bool $narrow): void
    {
    }
}
