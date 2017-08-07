<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SessionTokenController extends Controller
{
    public function __construct()
    {
        // tymczasowe rozwiazanie - nadpisujemy kontruktor z klasy bazowej
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function generateToken()
    {
        $secret = config('app.key');

        if (auth()->guest()) {
            return response()->json([
                'error' => 'No user logged in',
            ], 401);
        }

        $expirationDate = strtotime("+ 1 day");
        $token = auth()->user()->id . '|' . $expirationDate;
        $signedToken = $token . '|' . hash_hmac('sha256', $token, $secret);

        return response()->json([
            'token' => $signedToken,
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function verifyToken(Request $request)
    {
        $secret = config('app.key');
        $input = $request->input('token');
        $fragments = explode('|', $input);
        $token = $fragments[0] . '|' . $fragments[1];
        $signature = $fragments[2];
        $signatureCheck = hash_hmac('sha256', $token, $secret);

        return response()->json([
            'valid' => $signatureCheck === $signature,
        ]);
    }
}
