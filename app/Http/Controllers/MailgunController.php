<?php

namespace Coyote\Http\Controllers;

use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Illuminate\Http\Request;

class MailgunController extends Controller
{
    /**
     * @param UserRepository $user
     * @param Request $request
     */
    public function permanentFailure(UserRepository $user, Request $request)
    {
        logger()->debug($request->input()); // event-data->recipient

        /** @var \Coyote\User $result */
//        $result = $user->findBy('email', $request->input('event-data.recipient'), ['id', 'name', 'email', 'is_confirm']);
//
//        if ($result->is_confirm) {
//            $result->is_confirm = false;
//            $result->save();
//        }
    }
}
