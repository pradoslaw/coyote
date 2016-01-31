<?php

namespace Coyote\Http\Controllers\Auth;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Illuminate\Support\Facades\Auth;
use Coyote\Stream\Activities\Login as Stream_Login;
use Coyote\Stream\Activities\Create as Stream_Create;
use Coyote\Stream\Objects\Person as Stream_Person;
use Laravel\Socialite\Facades\Socialite;
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
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @param string $provider
     * @return Response
     */
    public function callback($provider)
    {
        $oauth = Socialite::driver($provider)->user();
        $user = $this->user->findWhere(['provider' => $provider, 'provider_id' => $oauth->getId()])->first();

        if (!$user) {
            $name = $oauth->getName() ?: $oauth->getNickName();

            if ($this->user->findByName($name)) {
                request()->session()->flash('error', 'Uuups. Niestety loginie ' . $name . ' jest już zajęty.');
                return redirect()->route('login');
            }

            $photoUrl = isset($oauth->avatar_original) ? $oauth->avatar_original : $oauth->getAvatar();
            $fileName = null;

            if ($photoUrl) {
                $fileName = uniqid() . '.png';
                $dir = public_path('storage/photo/');
                $path = $dir . $fileName;

                file_put_contents($path, file_get_contents($photoUrl), FILE_BINARY);

                $thumbnail = new Thumbnail\Thumbnail(new Thumbnail\Objects\Photo());
                $thumbnail->make($path);
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

        Auth::login($user, true);
        return redirect()->intended(route('home'));
    }
}