<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;

class SessionTokenController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function generateToken()
    {
        $secret = config('app.key');

        if (!$this->userId) {
            return response()->json([
                'error' => 'No user logged in',
            ], 401);
        }

        $expirationDate = strtotime("+ 1 day");
        $token = $this->userId . '|' . $expirationDate;
        $signedToken = $token . '|' . hash_hmac('sha256', $token, $secret);

        return response()->json([
            'token' => $signedToken,
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function verifyToken()
    {
        $secret = config('app.key');
        $input = $this->request->input('token');
        $fragments = explode('|', $input);
        $token = $fragments[0] . '|' . $fragments[1];
        $signature = $fragments[2];
        $signatureCheck = hash_hmac('sha256', $token, $secret);

        return response()->json([
            'valid' => $signatureCheck === $signature,
        ]);
    }
}
