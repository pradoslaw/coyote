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
        abort_if($request->header('X-Hub-Signature-256') === null, 401);
        logger()->debug((string) $request->getContent());

        abort_unless($this->verify($request->header('X-Hub-Signature-256'), $request->getContent()), 403);

        $sponsorship = $request->input('action') === 'created';

        $repository->sponsorship($sponsorship, $request->input('sender.id'), $request->input('sender.html_url'));
    }

    private function verify(string $signature, string $body): bool
    {
        return $signature === 'sha256=' . hash_hmac('sha256', $body, config('services.github.client_secret'));
    }
}
