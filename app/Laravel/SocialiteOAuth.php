<?php
namespace Coyote\Laravel;

use Coyote\Domain\OAuth\OAuth;
use Coyote\Domain\OAuth\User;
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

    public function user(string $provider): User
    {
        // this implementation implicitly takes "code" parameter
        // from laravel request.
        /** @var Socialite\Two\User $user */
        $user = $this->driver($provider)->stateless()->user();
        return new User(
            $user->getId(),
            $user->getEmail(),
            $user->getName() ?: $user->getNickName(),
            isset($user->avatar_original)
                ? $user->avatar_original
                : $user->getAvatar(),
        );
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
