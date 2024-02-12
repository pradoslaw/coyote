<?php
namespace Coyote\Laravel;

use Coyote\Domain\OAuth\OAuth;
use Laravel\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

class SocialiteOAuth implements OAuth
{
    public function loginUrl(string $provider): string
    {
        return $this
            ->driver($provider)
            ->redirect()
            ->getTargetUrl();
    }

    private function driver(string $provider): AbstractProvider
    {
        /** @var Socialite\Contracts\Factory $socialite */
        $socialite = app(Socialite\Contracts\Factory::class);
        /** @var AbstractProvider $driver */
        $driver = $socialite->driver($provider);
        return $driver;
    }
}
