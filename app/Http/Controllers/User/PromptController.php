<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Coyote\Repositories\Contracts\UserRepositoryInterface;

class PromptController extends Controller
{
    /**
     * @param Request $request
     * @param UserRepositoryInterface $user
     * @return $this
     */
    public function index(Request $request, UserRepositoryInterface $user)
    {
        $this->validate($request, ['q' => 'username']);
        return view('components.prompt')->with('users', $user->findByName($request['q']));
    }
}
