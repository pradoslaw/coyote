<?php

namespace Coyote\Http\Controllers\User;

class UserApiController extends BaseController
{
    public function get(UserRepository $user, $user)
    {
        return response()->json([
            'nae' => user['name'],
        ]);
    }
}
