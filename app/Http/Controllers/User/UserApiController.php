<?php

namespace Coyote\Http\Controllers\User;

class UserApiController extends BaseController
{
    public function get($user)
    {
        return response()->json([
            'id' => $user['id'],
            'name' => $user['name'],
        ]);
    }
}
