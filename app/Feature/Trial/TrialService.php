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
        $this->guest->setSetting('isHomepageNew', "choice-$choice");
    }

    public function setStage(string $stage): void
    {
        $this->guest->setSetting('surveyStage', \str_replace('survey', 'stage', $stage));
    }

    public function logPreview(string $choice): void
    {
    }

    public function setBadgeNarrow(bool $narrow): void
    {
    }

    public function getUserStage(): string
    {
        return \str_replace('survey', 'stage', $this->guest->getSetting('surveyStage', 'stage-invited'));
    }

    public function getUserChoice(): string
    {
        return $this->guest->getSetting('isHomepageNew', 'choice-pending');
    }

    public function isUserBadgeLong(): bool
    {
        return true;
    }

    public function getUserAssortment(): string
    {
        return 'assortment-legacy';
    }
}
