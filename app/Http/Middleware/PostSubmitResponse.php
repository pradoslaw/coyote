<?php

namespace Coyote\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class PostSubmitResponse
{
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
        if ($request->ajax() && $response->getStatusCode() === 200) {
            /** @var \Coyote\Post $post */
            $post = $response->getOriginalContent();
            $data = ['post' => ['text' => $post->html, 'attachments' => $post->attachments()->get()]];

            if ($request->user()->allow_sig && $post->user_id) {
                $parser = app('parser.sig');

                $user = $post->user()->withTrashed()->first(['sig']);

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
