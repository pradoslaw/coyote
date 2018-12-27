<?php

namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class PostSubmitResponse
{
    /**
     * @var UserRepository
     */
    private $user;

    /**
     * @param UserRepository $user
     */
    public function __construct(UserRepository $user)
    {
        $this->user = $user;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return \Illuminate\Http\RedirectResponse|Response
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var \Illuminate\Http\Response $response */
        $response = $next($request);

        // is this a quick edit (via ajax)?
        // we need to be sure, that response is really instance of Response class.
        // if there is an error, $response will be instance of JsonResponse.
        if ($request->ajax()) {
            $post = $response->getOriginalContent();
            $data = ['post' => ['text' => $post->html, 'attachments' => $post->attachments()->get()]];

            if ($request->user()->allow_sig && $post->user_id) {
                $parser = app('parser.sig');
                $user = $this->user->find($post->user_id, ['sig']);

                if ($user->sig) {
                    $data['post']['sig'] = $parser->parse($user->sig);
                }
            }

            return response()->view('forum.partials.text', $data);
        } elseif ($request->attributes->has('url')) {
            return redirect()->to($request->attributes->get('url'));
        } else {
            return $response;
        }
    }
}
