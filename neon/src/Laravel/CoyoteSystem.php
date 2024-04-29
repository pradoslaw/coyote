<?php
namespace Neon\Laravel;

use Coyote\Services\Guest;
use Illuminate\Foundation\Application;
use Neon\Persistence\System;

readonly class CoyoteSystem implements System
{
    public function __construct(private Application $application)
    {
    }

    // This logic is copied-and-pasted from ViewServiceProvider,
    // please, in the future extract common pieces between them.
    // we don't mind coupling with coyote here.

    public function darkTheme(): bool
    {
        /** @var Guest $guest */
        $guest = $this->application[Guest::class];
        return $guest->getSetting('lastColorScheme',
                $this->legacyLastColorScheme()) === 'dark';
    }

    private function legacyLastColorScheme(): ?string
    {
        /** @var Guest $guest */
        $guest = $this->application[Guest::class];
        return $guest->getSetting('dark.theme', true) ? 'dark' : 'light';
    }
}
