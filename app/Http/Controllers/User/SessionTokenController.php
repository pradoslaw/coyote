<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;

class SessionTokenController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        $secret = config('app.key');
        $userid = $this->userId;
        $token = hash_hmac('sha256', $userid, $secret);

        return response($token);
    }
}
