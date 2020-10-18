<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Resources\PromptResource;
use Illuminate\Http\Request;
use Coyote\Repositories\Contracts\UserRepositoryInterface;

class PromptController extends Controller
{
    /**
     * @param Request $request
     * @param UserRepositoryInterface $user
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request, UserRepositoryInterface $user)
    {
        $this->validate($request, ['q' => 'username']);

        $result = $user->lookupName($request['q']);

        PromptResource::withoutWrapping();

        if ($request->wantsJson()) {
            return PromptResource::collection($result);
        }

        return view('components.prompt')->with('users', $result);
    }
}
