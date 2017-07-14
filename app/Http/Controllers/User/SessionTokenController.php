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
        $userId = $this->userId;
        $expirationDate = strtotime("+ 1 day");
        $token = $userId . '|' . $expirationDate;
        $signedToken = $token . '|' . hash_hmac('sha256', $token, $secret);

        return response()->json([
            'token' => $signedToken,
        ]);
    }
}
