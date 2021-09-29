<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PushController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request,[
            'endpoint'    => 'required',
            'keys.auth'   => 'required',
            'keys.p256dh' => 'required'
        ]);

        $this->auth->updatePushSubscription($request->post('endpoint'), $request->input('keys.p256dh'), $request->input('keys.auth'));
    }
}
