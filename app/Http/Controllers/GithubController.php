<?php

namespace Coyote\Http\Controllers;

use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Illuminate\Http\Request;

class GithubController extends Controller
{
    const CREATED = 'created';
    const CANCELLED = 'cancelled';

    public function sponsorship(Request $request, UserRepository $repository)
    {
        $sponsorship = $request->input('action') === 'created' ? true : ($request->input('action') === self::CANCELLED ? false : null);

        $repository->sponsorship($sponsorship, $request->input('sender.id'), $request->input('sender.html_url'));
    }
}
