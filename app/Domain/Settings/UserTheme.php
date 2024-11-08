<?php
namespace Coyote\Domain\Settings;

use Coyote\Services\Guest;

class UserTheme
{
    public function __construct(private Guest $guest)
    {
    }

    public function isThemeDark(): bool
    {
        return $this->guest->getSetting('lastColorScheme', 'light') === 'dark';
    }

    public function theme(): string
    {
        return $this->isThemeDark() ? 'dark' : 'light';
    }

    public function themeMode(): string
    {
        return $this->guest->getSetting('colorScheme') ?? 'system';
    }
}
