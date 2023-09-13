<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Resources\PromptResource;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\View\View;

class PromptController extends Controller
{
    public function index(Request $request, UserRepositoryInterface $user): ResourceCollection|View
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
