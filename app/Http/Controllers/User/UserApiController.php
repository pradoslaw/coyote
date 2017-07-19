<?php

namespace Coyote\Http\Controllers\User;

class UserApiController extends BaseController
{
    /**
    * @param \Coyote\User $user
    */
    public function get($user)
    {
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
        ]);
    }
}
