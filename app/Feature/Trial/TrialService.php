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
    }

    public function logPreview(string $choice): void
    {
    }

    public function setBadgeNarrow(bool $narrow): void
    {
    }

    public function getUserStage(): string
    {
        return 'stage-invited';
    }

    public function getUserChoice(): string
    {
        return 'choice-pending'; // 'choice-legacy', 'choice-modern',
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
