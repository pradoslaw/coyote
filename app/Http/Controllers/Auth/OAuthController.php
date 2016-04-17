<?php

namespace Coyote\Http\Controllers\Auth;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Stream\Activities\Login as Stream_Login;
use Coyote\Stream\Activities\Create as Stream_Create;
use Coyote\Stream\Objects\Person as Stream_Person;
use Illuminate\Http\Response;
use Coyote\Thumbnail;

class OAuthController extends Controller
{
    /**
     * @var User
     */
    private $user;

    /**
     * OAuthController constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        parent::__construct();
        $this->user = $user;
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @param string $provider
     * @return Response
     */
    public function login($provider)
    {
        return $this->getSocialiteFactory()->driver($provider)->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @param string $provider
     * @return Response
     */
    public function callback($provider)
    {
        $oauth = $this->getSocialiteFactory()->driver($provider)->user();
        $user = $this->user->findWhere(['provider' => $provider, 'provider_id' => $oauth->getId()])->first();

        // if record does not exist, we must create new user in database
        if (!$user) {
            $name = $oauth->getName() ?: $oauth->getNickName();

            // it's important to check login name using case insensitive...
            if ($this->user->findByName($name)) {
                // komunikatu bledu nie mozemy przekazac w sesji poniewaz z jakiegos powodu
                // jest ona gubiona
                return redirect()->route('register', [
                    'error' => sprintf('Uuups. Niestety login %s jest już zajęty.', $name)
                ]);
            }

            $user = $this->user->findByEmail($oauth->getEmail());

            if ($user && $user->is_confirm) {
                // komunikatu bledu nie mozemy przekazac w sesji poniewaz z jakiegos powodu
                // jest ona gubiona
                return redirect()->route('register', [
                    'error' => sprintf(
                        'Adres e-mail %s jest już przypisany do użytkownika %s',
                        $oauth->getEmail(),
                        $user->name
                    )
                ]);
            }

            $photoUrl = isset($oauth->avatar_original) ? $oauth->avatar_original : $oauth->getAvatar();
            $fileName = null;

            if ($photoUrl) {
                $fileName = uniqid() . '.png';
                $path = config('filesystems.photo') . $fileName;

                $fs = $this->getFilesystemFactory();
                $fs->put($path, file_get_contents($photoUrl));

                $thumbnail = new Thumbnail\Thumbnail(new Thumbnail\Objects\Photo());
                $thumbnail->make(public_path('storage/' . $path));
            }

            $user = $this->user->create([
                'name' => $name,
                'email' => $oauth->getEmail(),
                'photo' => $fileName,
                'is_active' => 1,
                'is_confirm' => 1,
                'provider' => $provider,
                'provider_id' => $oauth->getId()
            ]);

            stream(Stream_Create::class, new Stream_Person($user->toArray()));
        } else {
            // put information into the activity stream...
            stream(Stream_Login::class);
        }

        auth()->login($user, true);
        return redirect()->intended(route('home'));
    }

    /**
     * @return \Illuminate\Contracts\Filesystem\Filesystem;
     */
    private function getFilesystemFactory()
    {
        return app('filesystem.disk');
    }

    /**
     * @return \Laravel\Socialite\Contracts\Factory
     */
    public function getSocialiteFactory()
    {
        return app(\Laravel\Socialite\Contracts\Factory::class);
    }
}
