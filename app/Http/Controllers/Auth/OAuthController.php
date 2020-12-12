<?php

namespace Coyote\Http\Controllers\Auth;

use Coyote\Events\UserSaved;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\MediaFactory;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\Stream\Activities\Login as Stream_Login;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Objects\Person as Stream_Person;
use Coyote\User;
use Laravel\Socialite\Contracts\Factory as Socialite;

class OAuthController extends Controller
{
    use MediaFactory;

    /**
     * @var UserRepository
     */
    private $user;

    /**
     * OAuthController constructor.
     * @param UserRepository $user
     */
    public function __construct(UserRepository $user)
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
        if (!$this->request->filled('code') || $this->request->filled('error')) {
            return redirect()->route('login', ['error' => $this->request->input('error_description')]);
        }

        $oauth = $this->getSocialiteFactory()->driver($provider)->stateless()->user();
        $user = $this->user->findWhere(['provider' => $provider, 'provider_id' => $oauth->getId()])->first();

        if (!$user) {
            $user = $this->user->findByEmail($oauth->getEmail());

            if ($user !== null) {
                // merge with existing user account
                $user->provider = $provider;
                $user->provider_id = $oauth->getId();
                $user->save();
            } else {
                $name = trim($oauth->getName() ?: $oauth->getNickName());

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

                $user = User::forceCreate([
                    'name' => $name,
                    'email' => $oauth->getEmail(),
                    'photo' => $filename,
                    'is_confirm' => 1,
                    'provider' => $provider,
                    'provider_id' => $oauth->getId(),
                    'guest_id' => $this->request->session()->get('guest_id')
                ]);
            }
        }

        if ($user->is_blocked) {
            return redirect()->route('login', ['error' => 'Konto zostało zablokowane.']);
        }

        auth()->login($user, true);

        if ($user->wasRecentlyCreated) {
            stream(Stream_Create::class, new Stream_Person($user));

            event(new UserSaved($user));
        }

        stream(Stream_Login::class);

        return redirect()->intended(route('home'));
    }

    /**
     * @return Socialite
     */
    public function getSocialiteFactory()
    {
        return app(Socialite::class);
    }
}
