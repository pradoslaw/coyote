<?php

namespace Coyote\Http\Controllers\User;

use Illuminate\Http\JsonResponse;

class UserApiController extends BaseController
{
    public function __construct()
    {
    }

    public function get(\Coyote\User $user): JsonResponse
    {
        return response()->json(['id' => $user->id, 'name' => $user->name,]);
    }
}
