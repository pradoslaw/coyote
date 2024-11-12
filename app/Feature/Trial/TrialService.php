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
        return $this->getUserChoice() === 'choice-modern';
    }

    public function setChoice(string $choice): void
    {
        $this->guest->setSetting('surveyChoice', $choice);
    }

    public function setStage(string $stage): void
    {
        $this->guest->setSetting('surveyStage', $stage);
    }

    public function logPreview(string $choice): void
    {
    }

    public function setBadgeNarrow(bool $narrow): void
    {
        $this->guest->getSetting('surveyBadgeNarrow', $narrow);
    }

    public function getUserStage(): string
    {
        return $this->guest->getSetting('surveyStage', 'stage-invited');
    }

    public function getUserChoice(): string
    {
        return $this->guest->getSetting('surveyChoice', 'choice-pending');
    }

    public function isUserBadgeLong(): bool
    {
        return !$this->guest->getSetting('surveyBadgeNarrow', false);
    }

    public function getUserAssortment(): string
    {
        return 'assortment-legacy';
    }
}
