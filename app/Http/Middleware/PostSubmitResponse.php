<?php

namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;

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
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var \Illuminate\Http\Response $response */
        $response = $next($request);

        // is this a quick edit (via ajax)?
        if ($request->ajax()) {
            $post = $response->getOriginalContent();
            $data = ['post' => ['text' => $post->text, 'attachments' => $post->attachments()->get()]];

            if ($request->user()->allow_sig && $post->user_id) {
                $parser = app('parser.sig');
                $user = $this->user->find($post->user_id, ['sig']);

                if ($user->sig) {
                    $data['post']['sig'] = $parser->parse($user->sig);
                }
            }
            return view('forum.partials.text', $data);
        } elseif ($request->attributes->has('url')) {
            return redirect()->to($request->attributes->get('url'));
        } else {
            return $response;
        }
    }
}
