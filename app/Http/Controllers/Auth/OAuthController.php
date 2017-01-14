<?php

namespace Coyote\Http\Controllers\Auth;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\MediaFactory;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Services\Stream\Activities\Login as Stream_Login;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Objects\Person as Stream_Person;
use Coyote\User;
use Laravel\Socialite\Contracts\Factory as Socialite;

class OAuthController extends Controller
{
    use MediaFactory;

    /**
     * @var User
     */
    private $user;

    /**
     * OAuthController constructor.
     * @param User $user
     */
    public function __construct(UserRepositoryInterface $user)
    {
        parent::__construct();
        $this->user = $user;
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @param string $provider
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function login($provider)
    {
        return $this->getSocialiteFactory()->driver($provider)->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback($provider)
    {
        $oauth = $this->getSocialiteFactory()->driver($provider)->user();
        $user = $this->user->findWhere(['provider' => $provider, 'provider_id' => $oauth->getId()])->first();

        if (!$user) {
            $user = $this->user->findByEmail($oauth->getEmail());

            if ($user !== null && $user->provider === null) {
                // merge with existing user account
                $user->provider = $provider;
                $user->provider_id = $oauth->getId();
                $user->save();
            } else {
                $name = $oauth->getName() ?: $oauth->getNickName();

                // it's important to check login name using case insensitive...
                if ($this->user->findByName($name)) {
                    // komunikatu bledu nie mozemy przekazac w sesji poniewaz z jakiegos powodu
                    // jest ona gubiona
                    return redirect()->route('register', [
                        'error' => sprintf('Uuups. Niestety login %s jest już zajęty.', $name)
                    ]);
                }

                // create new user in database
                $photoUrl = isset($oauth->avatar_original) ? $oauth->avatar_original : $oauth->getAvatar();
                $filename = null;

                if ($photoUrl) {
                    $media = $this->getMediaFactory()->make('photo')->put(file_get_contents($photoUrl));
                    $filename = $media->getFilename();
                }

                $user = $this->user->newUser([
                    'name' => $name,
                    'email' => $oauth->getEmail(),
                    'photo' => $filename,
                    'is_active' => 1,
                    'is_confirm' => 1,
                    'provider' => $provider,
                    'provider_id' => $oauth->getId()
                ]);
            }
        }

        if ($user->is_blocked || !$user->is_active) {
            return redirect()->route('login', [
                'error' => 'Konto zostało zablokowane.'
            ]);
        }

        return $this->doLogin($user);
    }

    /**
     * @return Socialite
     */
    public function getSocialiteFactory()
    {
        return app(Socialite::class);
    }

    /**
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    private function doLogin(User $user)
    {
        if ($user->wasRecentlyCreated) {
            stream(Stream_Create::class, new Stream_Person($user->toArray()));
        } else {
            stream(Stream_Login::class);
        }

        auth()->login($user, true);

        return redirect()->intended(route('home'));
    }
}
