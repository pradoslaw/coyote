<?php
namespace Coyote\Domain\Survey;

use Coyote\Services\Guest;

readonly class GuestSurvey
{
    public function __construct(private Guest $guest, private Clock $clock)
    {
    }

    public function setState(string $state): void
    {
        $this->setSetting('surveyState', $state);
    }

    public function setChoice(string $choice): void
    {
        $this->setSetting('postCommentStyle', $choice);
    }

    public function state(): string
    {
        return $this->normalizedState($this->guest->getSetting('surveyState'));
    }

    public function choice(): string
    {
        return $this->normalizedChoice($this->guest->getSetting('postCommentStyle'));
    }

    private function normalizedState(?string $state): string
    {
        if ($this->isState($state)) {
            return $state;
        }
        return 'survey-none';
    }

    private function isState(?string $state): bool
    {
        return \in_array($state, [
            'survey-none',
            'survey-invited',
            'survey-declined',
            'survey-accepted',
            'survey-instructed',
            'survey-gone',
        ]);
    }

    private function normalizedChoice(?string $style): string
    {
        if (\in_array($style, ['modern', 'legacy', 'none-legacy', 'none-modern'])) {
            return $style;
        }
        return 'none-legacy';
    }

    public function preview(string $value): void
    {
        $this->logValue("preview-$value");
    }

    private function setSetting(string $setting, string $value): void
    {
        $this->guest->setSetting($setting, $value);
        $this->logValue($value);
    }

    public function clearLog(): void
    {
        $this->guest->setSetting('surveyLog', []);
    }

    private function logValue(string $value): void
    {
        $this->guest->setSetting('surveyLog', [
            ...$this->guest->getSettings()['surveyLog'] ?? [],
            [$this->clock->time(), $value],
        ]);
    }
}
