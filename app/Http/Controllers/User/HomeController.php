<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Events\UserSaved;
use Coyote\Http\Controllers\User\Menu\AccountMenu;
use Coyote\Http\Factories\MediaFactory;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends BaseController
{
    use AccountMenu, MediaFactory;

    public function index(UserRepositoryInterface $user): View
    {
        return $this->view('user.home', [
          'rank'        => $user->rank($this->userId),
          'total_users' => $user->countUsersWithReputation(),
          'ip'          => request()->ip()
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        $this->validate($request, [
          'photo' => 'required|mimes:jpeg,jpg,png,gif'
        ]);
        $media = $this->auth->photo->upload($request->file('photo'));
        $this->auth->save();
        event(new UserSaved($this->auth));
        return response()
          ->json(['url' => (string)$media->url()]);
    }

    public function delete(): void
    {
        $this->auth->photo = null;
        $this->auth->save();
    }
}
